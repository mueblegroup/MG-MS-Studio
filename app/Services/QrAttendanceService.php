<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\ClassSessionAssignment;
use App\Models\Order;
use App\Models\PlanSession;
use App\Models\User;
use App\Models\UserPlan;
use App\Support\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class QrAttendanceService
{
    public function session(string $kind, int $id, bool $lock = false): ClassSession|PlanSession
    {
        $studio = app(TenantManager::class)->current();
        abort_unless($studio && $studio->isActive(), 403, 'Open check-in from an active studio portal.');
        $model = match ($kind) {
            'class' => ClassSession::class,
            'plan' => PlanSession::class,
            default => abort(404),
        };

        $query = $model::query()->where('studio_id', $studio->id);
        if ($lock) {
            $query->lockForUpdate();
        }
        $session = $query->findOrFail($id);
        $parent = $session instanceof ClassSession ? $session->classModel : $session->plan;
        abort_unless($parent && (int) $parent->studio_id === (int) $studio->id, 404);

        return $session;
    }

    public function authorizeStaff(User $user, ClassSession|PlanSession $session): void
    {
        $studio = app(TenantManager::class)->current();
        $member = (int) $user->studio_id === (int) $session->studio_id;
        $owner = $user->role === 'admin' && ! $user->studio_id
            && (int) $studio?->owner_user_id === (int) $user->id;
        abort_unless($member || $owner, 403);
        $parent = $session instanceof ClassSession ? $session->classModel : $session->plan;
        abort_unless($user->role === 'admin'
            || ($user->role === 'teacher' && (int) $parent->teacher_id === (int) $user->id), 403);
    }

    public function windowMessage(ClassSession|PlanSession $session): ?string
    {
        if ($session instanceof ClassSession && $session->isCancelled()) {
            return 'This class has been cancelled.';
        }
        if ($session instanceof PlanSession && ! $session->plan->is_active) {
            return 'This plan is no longer active.';
        }
        if (! $session->start_time || ! $session->end_time || $session->end_time->lte($session->start_time)) {
            return 'This session needs a valid start and end time before QR check-in can open.';
        }
        if (now()->lt($session->start_time->copy()->subMinutes(15))) {
            return 'Check-in opens 15 minutes before the class starts.';
        }
        if (now()->gte($session->end_time)) {
            return 'Check-in is closed. The class has ended.';
        }

        return null;
    }

    public function openQr(User $user, string $kind, int $id, bool $replace): void
    {
        DB::transaction(function () use ($user, $kind, $id, $replace) {
            $session = $this->session($kind, $id, true);
            $this->authorizeStaff($user, $session);
            abort_if($message = $this->windowMessage($session), 403, $message);
            if ($replace || ! $session->attendance_qr_version) {
                $session->forceFill(['attendance_qr_version' => Str::random(64)])->save();
                app(AuditLogService::class)->record('attendance.qr_opened', $user, [
                    'session_type' => $kind, 'session_id' => $id, 'replaced' => $replace,
                ]);
            }
        });
    }

    public function fingerprint(ClassSession|PlanSession $session): string
    {
        // Also guards schedule changes made by bulk updates that bypass model events.
        return hash('sha256', implode('|', [
            $session->studio_id, $session->class_id ?? $session->plan_id,
            $session->getRawOriginal('start_time'), $session->getRawOriginal('end_time'),
        ]));
    }

    public function checkInUrl(string $kind, ClassSession|PlanSession $session): ?string
    {
        if ($this->windowMessage($session) || ! $session->attendance_qr_version) {
            return null;
        }

        return URL::temporarySignedRoute('attendance.check-in', $session->end_time, [
            'kind' => $kind, 'id' => $session->id, 'v' => $session->attendance_qr_version,
            'schedule' => $this->fingerprint($session),
        ]);
    }

    public function validateScan(Request $request, ClassSession|PlanSession $session): void
    {
        abort_unless($request->hasValidSignature(), 403, 'This QR code is invalid or expired. Ask your teacher for the current code.');
        abort_if($message = $this->windowMessage($session), 403, $message);
        abort_unless(is_string($request->query('v')) && filled($session->attendance_qr_version)
            && hash_equals($session->attendance_qr_version, $request->query('v'))
            && is_string($request->query('schedule'))
            && hash_equals($this->fingerprint($session), $request->query('schedule')), 403,
            'This QR code has been replaced or the class has changed. Scan the current code.');
    }

    public function attendanceKey(User $student, ClassSession|PlanSession $session, bool $lock = false): array
    {
        abort_unless($student->role === 'student' && ! $student->trashed()
            && (int) $student->studio_id === (int) $session->studio_id, 403,
            'Please sign in with your student account for this studio.');

        if ($session instanceof ClassSession) {
            $assignment = ClassSessionAssignment::query()
                ->where('studio_id', $session->studio_id)->where('class_session_id', $session->id)
                ->where('user_id', $student->id)->where('status', 'assigned')->orderBy('id')
                ->when($lock, fn ($q) => $q->lockForUpdate())->first();
            abort_unless($assignment, 403, 'You do not have a confirmed booking for this session. Contact your teacher.');

            // Paid orders grant assignments; subscription attendance additionally requires payment
            // for THIS occurrence, even when the overall subscription is active or later cancelled.
            if ($session->classModel->isSubscriptionClass()) {
                abort_unless($this->hasPaidOrder($student, $session, ClassSession::class, $session->id, $lock), 403,
                    'Payment for this subscription session must be confirmed before check-in.');
            } elseif (! $assignment->assigned_by) {
                abort_unless($this->hasPaidOrder($student, $session, ClassSession::class, $session->id, $lock), 403,
                    'Payment for this session must be confirmed before check-in.');
            }

            return ['class_session_assignment_id' => $assignment->id];
        }

        $date = $session->start_time->toDateString();
        $active = UserPlan::query()->where('studio_id', $session->studio_id)
            ->where('plan_id', $session->plan_id)->where('user_id', $student->id)->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_on')->orWhereDate('starts_on', '<=', $date))
            ->where(fn ($q) => $q->whereNull('ends_on')->orWhereDate('ends_on', '>=', $date))
            ->when($lock, fn ($q) => $q->lockForUpdate())->first();
        // Active user_plans are explicitly granted by staff or fulfilled from a paid order.
        abort_unless($active, 403, 'You do not have an active plan covering this session. Contact your teacher.');

        return ['plan_session_id' => $session->id, 'user_id' => $student->id];
    }

    private function hasPaidOrder(User $student, ClassSession|PlanSession $session, string $type, int $id, bool $lock): bool
    {
        return Order::query()->where('studio_id', $session->studio_id)->where('user_id', $student->id)
            ->where('status', 'paid')->whereNotNull('fulfilled_at')
            ->whereHas('items', fn ($q) => $q->where('studio_id', $session->studio_id)
                ->whereIn('purchasable_type', [$type, class_basename($type)])->where('purchasable_id', $id))
            ->when($lock, fn ($q) => $q->lockForUpdate())->first() !== null;
    }

    public function confirm(Request $request, string $kind, int $id): bool
    {
        return DB::transaction(function () use ($request, $kind, $id) {
            // Serialize scans for this session; recheck expiry AFTER acquiring the lock.
            $session = $this->session($kind, $id, true);
            $this->validateScan($request, $session);
            $key = $this->attendanceKey($request->user(), $session, true);
            $attendance = $this->existingAttendance($request->user(), $session, true);
            if ($attendance?->status === 'attended') {
                return false;
            }
            abort_if($attendance !== null, 409, 'Attendance was already recorded by staff. Ask your teacher to correct it.');
            // Eligibility/attendance locks may have waited past the cutoff.
            $this->validateScan($request, $session);
            Attendance::create($key + [
                'studio_id' => $session->studio_id, 'user_id' => $request->user()->id,
                'status' => 'attended', 'attended_at' => now(),
            ]);
            app(AuditLogService::class)->record('attendance.qr_checked_in', $request->user(), [
                'session_type' => $kind, 'session_id' => $id,
            ], $request);

            return true;
        }, 3);
    }

    public function existingAttendance(User $student, ClassSession|PlanSession $session, bool $lock = false): ?Attendance
    {
        return Attendance::query()->where('studio_id', $session->studio_id)->where('user_id', $student->id)
            ->when($session instanceof ClassSession,
                fn ($q) => $q->whereIn('class_session_assignment_id', ClassSessionAssignment::withTrashed()
                    ->where('studio_id', $session->studio_id)->where('class_session_id', $session->id)
                    ->where('user_id', $student->id)->select('id')),
                fn ($q) => $q->where('plan_session_id', $session->id))
            ->when($lock, fn ($q) => $q->lockForUpdate())->orderBy('id')->first();
    }

    public function attendees(ClassSession|PlanSession $session): array
    {
        return Attendance::query()->where('attendances.studio_id', $session->studio_id)
            ->where('attendances.status', 'attended')
            ->when($session instanceof ClassSession,
                fn ($q) => $q->whereIn('class_session_assignment_id', ClassSessionAssignment::query()
                    ->where('studio_id', $session->studio_id)->where('class_session_id', $session->id)->select('id')),
                fn ($q) => $q->where('plan_session_id', $session->id))
            ->join('users', 'users.id', '=', 'attendances.user_id')
            ->where('users.studio_id', $session->studio_id)->whereNull('users.deleted_at')
            ->orderByDesc('attendances.attended_at')->get(['users.name', 'attendances.attended_at'])
            ->map(fn ($row) => ['name' => $row->name, 'time' => $row->attended_at?->format('H:i')])->all();
    }
}
