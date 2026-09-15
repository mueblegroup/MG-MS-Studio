<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Attendance;
use App\Models\ClassCard;
use App\Models\ClassModel;
use App\Models\MigrationRecord;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\PlanSession;
use App\Models\User;
use App\Models\UserClassCard;
use App\Models\UserPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MigrationApiController extends BaseApiController
{
    public function records(Request $request): JsonResponse
    {
        $query = MigrationRecord::query()->latest();

        foreach (['source_system', 'entity_type', 'source_id', 'target_type', 'target_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        return $this->paginated(
            $query->paginate($request->integer('per_page', 100)),
            'Migration records loaded.'
        );
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_system' => ['required', 'string', 'max:100'],
            'entity_type' => ['required', 'string', 'max:100'],
            'source_id' => ['required', 'string', 'max:191'],
            'target_type' => ['nullable', 'string', 'max:255'],
            'target_id' => ['nullable', 'integer', 'min:1'],
            'checksum' => ['nullable', 'string', 'size:64'],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->upsertRecord($validated);

        return $this->success($record, 'Migration mapping registered.');
    }

    public function user(Request $request): JsonResponse
    {
        $source = $this->validateSource($request, 'user');
        if ($existing = $this->existingRecord($source)) {
            return $this->idempotent($existing);
        }

        $studioId = $this->studioId();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('studio_id', $studioId)),
            ],
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8', 'prohibited_with:legacy_password_hash'],
            'legacy_password_hash' => [
                'nullable',
                'string',
                'size:60',
                'prohibited_with:password',
                'regex:/^\$2y\$(0[4-9]|[12][0-9]|3[01])\$[\.\/A-Za-z0-9]{53}$/',
            ],
            'metadata' => ['nullable', 'array'],
        ]);

        $preservesLegacyPassword = ! empty($validated['legacy_password_hash']);
        $hasPassword = ! empty($validated['password']) || $preservesLegacyPassword;

        [$user, $record] = DB::transaction(function () use ($validated, $source, $preservesLegacyPassword) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'phone_number' => $validated['phone_number'] ?? null,
                // Use the normal model path for new plaintext passwords. Legacy hashes are
                // applied directly below so the User model's "hashed" cast cannot hash them again.
                'password' => $validated['password'] ?? Str::random(48),
            ]);

            if ($preservesLegacyPassword) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->where('studio_id', $this->studioId())
                    ->update(['password' => $validated['legacy_password_hash']]);

                $user->refresh();
            }

            $record = $this->recordTarget($source, $user, $validated['metadata'] ?? []);

            return [$user, $record];
        });

        return $this->success([
            'target' => $user,
            'migration_record' => $record,
            'password_setup_required' => ! $hasPassword,
            'legacy_password_preserved' => $preservesLegacyPassword,
        ], 'Legacy user imported.', 201);
    }

    public function userPlan(Request $request): JsonResponse
    {
        $source = $this->validateSource($request, 'user_plan');
        if ($existing = $this->existingRecord($source)) {
            return $this->idempotent($existing);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'plan_id' => ['required', 'integer'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $plan = Plan::query()->findOrFail($validated['plan_id']);

        [$userPlan, $record] = DB::transaction(function () use ($validated, $source, $user, $plan) {
            $userPlan = UserPlan::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'starts_on' => $validated['starts_on'] ?? null,
                'ends_on' => $validated['ends_on'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return [$userPlan, $this->recordTarget($source, $userPlan, $validated['metadata'] ?? [])];
        });

        return $this->success(['target' => $userPlan, 'migration_record' => $record], 'Legacy plan membership imported.', 201);
    }

    public function classCardPurchase(Request $request): JsonResponse
    {
        $source = $this->validateSource($request, 'classcard_purchase');
        if ($existing = $this->existingRecord($source)) {
            return $this->idempotent($existing);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'class_card_id' => ['required', 'integer'],
            'purchased_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'classes_remaining' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $card = ClassCard::query()->findOrFail($validated['class_card_id']);

        [$purchase, $record] = DB::transaction(function () use ($validated, $source, $user, $card) {
            $purchase = UserClassCard::create([
                'user_id' => $user->id,
                'class_card_id' => $card->id,
                'purchased_at' => $validated['purchased_at'] ?? now(),
                'expires_at' => $validated['expires_at'] ?? null,
                'classes_remaining' => $validated['classes_remaining'],
                'status' => $validated['status'] ?? 'active',
            ]);

            return [$purchase, $this->recordTarget($source, $purchase, $validated['metadata'] ?? [])];
        });

        return $this->success(['target' => $purchase, 'migration_record' => $record], 'Legacy class card purchase imported.', 201);
    }

    public function attendance(Request $request): JsonResponse
    {
        $source = $this->validateSource($request, 'attendance');
        if ($existing = $this->existingRecord($source)) {
            return $this->idempotent($existing);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'plan_session_id' => ['nullable', 'integer'],
            'class_session_assignment_id' => ['nullable', 'integer'],
            'booking_id' => ['nullable', 'integer'],
            'attended_at' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:50'],
            'metadata' => ['nullable', 'array'],
        ]);

        User::query()->findOrFail($validated['user_id']);

        if (! empty($validated['plan_session_id'])) {
            PlanSession::query()
                ->where('studio_id', $this->studioId())
                ->findOrFail($validated['plan_session_id']);
        }

        [$attendance, $record] = DB::transaction(function () use ($validated, $source) {
            $attendance = Attendance::create([
                'studio_id' => $this->studioId(),
                'user_id' => $validated['user_id'],
                'plan_session_id' => $validated['plan_session_id'] ?? null,
                'class_session_assignment_id' => $validated['class_session_assignment_id'] ?? null,
                'booking_id' => $validated['booking_id'] ?? null,
                'attended_at' => $validated['attended_at'] ?? null,
                'status' => $validated['status'],
            ]);

            return [$attendance, $this->recordTarget($source, $attendance, $validated['metadata'] ?? [])];
        });

        return $this->success(['target' => $attendance, 'migration_record' => $record], 'Legacy attendance imported.', 201);
    }

    public function order(Request $request): JsonResponse
    {
        $source = $this->validateSource($request, 'order');
        if ($existing = $this->existingRecord($source)) {
            return $this->idempotent($existing);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'currency' => ['nullable', 'string', 'size:3'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'paid', 'cancelled', 'failed'])],
            'provider_reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'items' => ['nullable', 'array'],
            'items.*.type' => ['required_with:items', Rule::in(['plan', 'classcard', 'class'])],
            'items.*.id' => ['required_with:items', 'integer'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.currency' => ['nullable', 'string', 'size:3'],
            'items.*.meta' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $items = $this->validatedOrderItems($validated['items'] ?? []);

        [$order, $record] = DB::transaction(function () use ($validated, $source, $user, $items) {
            // Historical imports must never emit live payment-due/success notifications.
            $order = Order::withoutEvents(fn () => Order::create([
                'user_id' => $user->id,
                'currency' => strtoupper($validated['currency'] ?? 'MYR'),
                'subtotal' => $validated['subtotal'],
                'total' => $validated['total'],
                'status' => $validated['status'],
                'payment_provider' => 'legacy',
                'billing_reason' => 'legacy_migration',
                'provider_reference' => $validated['provider_reference'] ?? $source['source_id'],
                'paid_at' => $validated['paid_at'] ?? null,
                'fulfilled_at' => $validated['status'] === 'paid' ? ($validated['paid_at'] ?? now()) : null,
            ]));

            foreach ($items as $item) {
                OrderItem::create([
                    'studio_id' => $this->studioId(),
                    'order_id' => $order->id,
                    'purchasable_type' => $item['class'],
                    'purchasable_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'currency' => $item['currency'],
                    'meta' => array_merge($item['meta'], ['migration_source' => $source['source_system']]),
                ]);
            }

            return [$order->load('items'), $this->recordTarget($source, $order, $validated['metadata'] ?? [])];
        });

        return $this->success(['target' => $order, 'migration_record' => $record], 'Legacy order imported without payment side effects.', 201);
    }

    public function payment(Request $request): JsonResponse
    {
        $source = $this->validateSource($request, 'payment');
        if ($existing = $this->existingRecord($source)) {
            return $this->idempotent($existing);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'order_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['required', Rule::in(['pending', 'paid', 'failed', 'cancelled'])],
            'reference' => ['nullable', 'string', 'max:255'],
            'provider_reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'payload' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $order = ! empty($validated['order_id']) ? Order::query()->findOrFail($validated['order_id']) : null;

        [$payment, $record] = DB::transaction(function () use ($validated, $source, $user, $order) {
            $payment = Payment::create([
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'amount' => $validated['amount'],
                'currency' => strtoupper($validated['currency'] ?? 'MYR'),
                'method' => 'legacy',
                'provider' => 'legacy',
                'reference' => $validated['reference'] ?? $source['source_id'],
                'provider_reference' => $validated['provider_reference'] ?? null,
                'payload' => array_merge($validated['payload'] ?? [], [
                    'migration_source' => $source['source_system'],
                    'migration_source_id' => $source['source_id'],
                ]),
                'paid_at' => $validated['paid_at'] ?? null,
                'status' => $validated['status'],
            ]);

            return [$payment, $this->recordTarget($source, $payment, $validated['metadata'] ?? [])];
        });

        return $this->success(['target' => $payment, 'migration_record' => $record], 'Legacy payment imported without contacting a payment gateway.', 201);
    }

    private function validateSource(Request $request, string $entityType): array
    {
        $validated = $request->validate([
            'source_system' => ['required', 'string', 'max:100'],
            'source_id' => ['required', 'string', 'max:191'],
            'checksum' => ['nullable', 'string', 'size:64'],
        ]);

        return [
            'source_system' => $validated['source_system'],
            'entity_type' => $entityType,
            'source_id' => $validated['source_id'],
            'checksum' => $validated['checksum'] ?? null,
        ];
    }

    private function existingRecord(array $source): ?MigrationRecord
    {
        return MigrationRecord::query()
            ->where('source_system', $source['source_system'])
            ->where('entity_type', $source['entity_type'])
            ->where('source_id', $source['source_id'])
            ->first();
    }

    private function recordTarget(array $source, Model $target, array $metadata = []): MigrationRecord
    {
        return $this->upsertRecord(array_merge($source, [
            'target_type' => $target::class,
            'target_id' => $target->getKey(),
            'metadata' => $metadata,
        ]));
    }

    private function upsertRecord(array $attributes): MigrationRecord
    {
        return MigrationRecord::query()->updateOrCreate(
            [
                'source_system' => $attributes['source_system'],
                'entity_type' => $attributes['entity_type'],
                'source_id' => (string) $attributes['source_id'],
            ],
            [
                'target_type' => $attributes['target_type'] ?? null,
                'target_id' => $attributes['target_id'] ?? null,
                'checksum' => $attributes['checksum'] ?? null,
                'metadata' => $attributes['metadata'] ?? null,
            ]
        );
    }

    private function idempotent(MigrationRecord $record): JsonResponse
    {
        return $this->success([
            'migration_record' => $record,
            'idempotent_replay' => true,
        ], 'Source record was already imported.');
    }

    private function studioId(): int
    {
        $studioId = function_exists('current_studio_id') ? current_studio_id() : null;
        abort_unless($studioId, 400, 'A studio tenant must be resolved before migration.');

        return (int) $studioId;
    }

    private function validatedOrderItems(array $items): array
    {
        $map = [
            'plan' => Plan::class,
            'classcard' => ClassCard::class,
            'class' => ClassModel::class,
        ];

        return collect($items)->map(function (array $item) use ($map) {
            $class = $map[$item['type']];
            $target = $class::query()->findOrFail($item['id']);

            return [
                'class' => $class,
                'id' => $target->getKey(),
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'],
                'currency' => strtoupper($item['currency'] ?? 'MYR'),
                'meta' => $item['meta'] ?? [],
            ];
        })->all();
    }
}
