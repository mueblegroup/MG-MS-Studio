<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClassCard;
use App\Models\User;
use App\Models\UserClassCard;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserClassCardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $purchases = UserClassCard::query()
            ->with(['user:id,name,email', 'card:id,name,total_classes,validity_weeks,price'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($searchQuery) use ($search) {
                    $searchQuery->whereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('card', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.classcards.classcard-purchases.index', compact('purchases', 'search', 'perPage'));
    }

    public function create(Request $request)
    {
        $cards = ClassCard::where('is_active', true)->orderBy('name')->get();
        $students = User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.classcards.classcard-purchases.create', compact('cards', 'students'));
    }

    public function store(Request $request)
    {
        $studioId = (int) current_studio_id();
        abort_if($studioId <= 0, 403, 'Studio context is required.');

        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('studio_id', $studioId)->where('role', 'student')->whereNull('deleted_at')),
            ],
            'class_card_id' => [
                'required',
                Rule::exists('class_cards', 'id')->where(fn ($q) => $q->where('studio_id', $studioId)->where('is_active', true)),
            ],
            'purchased_at' => 'nullable|date',
            'classes_remaining' => 'nullable|integer|min:0',
            'expires_at' => 'nullable|date|after_or_equal:purchased_at',
        ]);

        $card = ClassCard::findOrFail($validated['class_card_id']);
        $purchasedAt = !empty($validated['purchased_at'])
            ? Carbon::parse($validated['purchased_at'])
            : now();
        $expiresAt = !empty($validated['expires_at'])
            ? Carbon::parse($validated['expires_at'])
            : $purchasedAt->copy()->addWeeks((int) $card->validity_weeks);
        $classesRemaining = array_key_exists('classes_remaining', $validated) && $validated['classes_remaining'] !== null
            ? (int) $validated['classes_remaining']
            : (int) $card->total_classes;

        UserClassCard::create([
            'studio_id' => $studioId,
            'user_id' => (int) $validated['user_id'],
            'class_card_id' => (int) $card->id,
            'purchased_at' => $purchasedAt,
            'expires_at' => $expiresAt,
            'classes_remaining' => $classesRemaining,
            'status' => $expiresAt->lt(now()) ? 'expired' : 'active',
        ]);

        return redirect()
            ->route('admin.classcards.classcard-purchases')
            ->with('success', 'Class card assigned to student.');
    }

    public function show(UserClassCard $userClassCard)
    {
        return view('admin.classcards.classcard-purchases.show', compact('userClassCard'));
    }

    public function edit(UserClassCard $userClassCard)
    {
        $cards = ClassCard::where('is_active', true)->orderBy('name')->get();
        $students = User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.classcards.classcard-purchases.edit', compact('userClassCard', 'cards', 'students'));
    }

    public function update(Request $request, UserClassCard $userClassCard)
    {
        if ($request->boolean('extension_only')) {
            return $this->extendExpiry($request, $userClassCard);
        }

        $studioId = (int) current_studio_id();
        abort_if($studioId <= 0, 403, 'Studio context is required.');

        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('studio_id', $studioId)->where('role', 'student')->whereNull('deleted_at')),
            ],
            'class_card_id' => [
                'required',
                Rule::exists('class_cards', 'id')->where(fn ($q) => $q->where('studio_id', $studioId)->where('is_active', true)),
            ],
            'purchased_at' => 'nullable|date',
            'classes_remaining' => 'nullable|integer|min:0',
            'expires_at' => 'nullable|date|after_or_equal:purchased_at',
        ]);

        $card = ClassCard::findOrFail($validated['class_card_id']);
        $purchasedAt = !empty($validated['purchased_at'])
            ? Carbon::parse($validated['purchased_at'])
            : ($userClassCard->purchased_at ?: now());
        $expiresAt = !empty($validated['expires_at'])
            ? Carbon::parse($validated['expires_at'])
            : ($userClassCard->expires_at ?: $purchasedAt->copy()->addWeeks((int) $card->validity_weeks));
        $classesRemaining = array_key_exists('classes_remaining', $validated) && $validated['classes_remaining'] !== null
            ? (int) $validated['classes_remaining']
            : (int) $userClassCard->classes_remaining;

        $userClassCard->update([
            'user_id' => (int) $validated['user_id'],
            'class_card_id' => (int) $card->id,
            'purchased_at' => $purchasedAt,
            'expires_at' => $expiresAt,
            'classes_remaining' => $classesRemaining,
            'status' => $expiresAt->lt(now()) ? 'expired' : 'active',
        ]);

        return redirect()->route('admin.classcards.classcard-purchases')->with('success', 'Class card assignment updated.');
    }

    public function extendExpiry(Request $request, UserClassCard $userClassCard)
    {
        $studioId = (int) current_studio_id();
        abort_if($studioId <= 0, 403, 'Studio context is required.');
        abort_unless((int) $userClassCard->studio_id === $studioId, 404);

        $validated = $request->validate([
            'extension' => 'required|in:1_week,2_weeks,1_month,3_months,custom',
            'new_expiry_date' => 'nullable|required_if:extension,custom|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldExpiry = $userClassCard->expires_at?->copy();
        $base = $oldExpiry && $oldExpiry->isFuture() ? $oldExpiry->copy() : now();

        $newExpiry = match ($validated['extension']) {
            '1_week' => $base->copy()->addWeek(),
            '2_weeks' => $base->copy()->addWeeks(2),
            '1_month' => $base->copy()->addMonth(),
            '3_months' => $base->copy()->addMonths(3),
            'custom' => Carbon::parse($validated['new_expiry_date'])->endOfDay(),
        };

        $minimum = $oldExpiry && $oldExpiry->isFuture() ? $oldExpiry : now();
        if ($newExpiry->lte($minimum)) {
            throw ValidationException::withMessages([
                'new_expiry_date' => 'The new expiry must be later than the current expiry date, or later than today for an expired card.',
            ]);
        }

        $newStatus = (int) $userClassCard->classes_remaining <= 0 ? 'completed' : 'active';

        $userClassCard->update([
            'expires_at' => $newExpiry,
            'status' => $newStatus,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'studio_id' => $studioId,
            'event' => 'class_card_expiry_extended',
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'user_class_card_id' => $userClassCard->id,
                'class_card_id' => $userClassCard->class_card_id,
                'student_user_id' => $userClassCard->user_id,
                'old_expiry' => $oldExpiry?->toIso8601String(),
                'new_expiry' => $newExpiry->toIso8601String(),
                'extension' => $validated['extension'],
                'reason' => $validated['reason'] ?? null,
                'classes_remaining' => (int) $userClassCard->classes_remaining,
                'resulting_status' => $newStatus,
            ],
        ]);

        return back()->with('success', 'Class card expiry extended to '.$newExpiry->format('Y-m-d').'.');
    }

    public function destroy(UserClassCard $userClassCard)
    {
        $userClassCard->delete();

        return redirect()->route('admin.classcards.classcard-purchases')->with('success', 'Purchase record removed.');
    }
}
