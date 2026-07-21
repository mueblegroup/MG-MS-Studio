<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\StudioSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $perPage = max(10, min(100, (int) $request->query('per_page', 10)));

        $classes = ClassModel::query()
            ->with(['teacher:id,name,email'])
            ->withCount([
                'sessions',
                'studioSubscriptions as active_subscriptions_count' => fn ($query) => $query->whereIn('status', ['active', 'trialing', 'past_due']),
            ])
            ->withMin(['sessions as first_session_at' => fn ($query) => $query->where('status', '!=', 'cancelled')], 'start_time')
            ->withMax(['sessions as last_session_at' => fn ($query) => $query->where('status', '!=', 'cancelled')], 'start_time')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('teacher', fn ($teacher) => $teacher
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.classes.index', compact('classes', 'search', 'perPage'));
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.classes.create', compact('teachers'));
    }

    public function edit(ClassSession $classSession)
    {
        $classSession->load(['classModel.teacher:id,name,email']);
        $teachers = User::where('role', 'teacher')->orderBy('name')->get(['id', 'name', 'email']);
        $hasActiveSubscriptions = $classSession->classModel->studioSubscriptions()
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->exists();

        return view('admin.classes.edit', [
            'session' => $classSession,
            'class' => $classSession->classModel,
            'teachers' => $teachers,
            'hasActiveSubscriptions' => $hasActiveSubscriptions,
        ]);
    }

    public function update(Request $request, ClassSession $classSession)
    {
        $classSession->load('classModel');
        $class = $classSession->classModel;
        $hasActiveSubscriptions = $class->studioSubscriptions()
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->exists();

        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:1|max:1000',
            'class_type' => 'required|in:single,recurring,subscription',
            'billing_interval' => 'nullable|required_if:class_type,subscription|in:day,week,month,year',
            'subscription_grace_days' => 'nullable|integer|min:0|max:30',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'venue_name' => 'nullable|string|max:255',
            'confirm_danger' => 'nullable|accepted',
            'change_reason' => 'nullable|string|min:10|max:1000',
        ]);

        if ($hasActiveSubscriptions) {
            if ($validated['class_type'] !== $class->type) {
                throw ValidationException::withMessages(['class_type' => 'Class type cannot be changed while subscriptions are active.']);
            }

            if ((float) $validated['price'] !== (float) $class->price) {
                throw ValidationException::withMessages(['price' => 'Price cannot be changed while subscriptions are active. Create a new subscription class for a new price.']);
            }

            if ($class->type === 'subscription' && $validated['billing_interval'] !== $class->billing_interval) {
                throw ValidationException::withMessages(['billing_interval' => 'Billing interval cannot be changed while subscriptions are active.']);
            }
        }

        $newStart = Carbon::parse($validated['date'].' '.$validated['start_time']);
        $newEnd = Carbon::parse($validated['date'].' '.$validated['end_time']);
        $scheduleChanged = ! $classSession->start_time->equalTo($newStart) || ! $classSession->end_time->equalTo($newEnd);

        if ($hasActiveSubscriptions && $scheduleChanged) {
            if (! $request->boolean('confirm_danger') || empty($validated['change_reason'])) {
                throw ValidationException::withMessages([
                    'change_reason' => 'A reason and confirmation are required when rescheduling a subscribed class session.',
                ]);
            }

            $this->assertSafeSubscriptionSequence($classSession, $newStart);
        }

        DB::transaction(function () use ($validated, $classSession, $class, $newStart, $newEnd, $scheduleChanged) {
            $class->update([
                'name' => $validated['class_name'],
                'teacher_id' => $validated['teacher_id'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'capacity' => $validated['capacity'] ?? null,
                'type' => $validated['class_type'],
                'billing_interval' => $validated['class_type'] === 'subscription' ? $validated['billing_interval'] : null,
                'subscription_grace_days' => $validated['subscription_grace_days'] ?? 3,
            ]);

            $sessionUpdates = [
                'start_time' => $newStart,
                'end_time' => $newEnd,
                'capacity' => $validated['capacity'] ?? null,
                'venue_name' => $validated['venue_name'] ?? null,
            ];

            if ($scheduleChanged) {
                $sessionUpdates += [
                    'status' => 'rescheduled',
                    'change_type' => 'rescheduled',
                    'change_reason' => $validated['change_reason'] ?? null,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                ];
            }

            $classSession->update($sessionUpdates);
        });

        return redirect()->route('admin.subscription-classes.show', $class->id)
            ->with('success', 'Class session updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'teacher_id' => 'required|exists:users,id',
            'description' => 'nullable|string|max:5000',
            'class_type' => 'required|in:single,recurring,subscription',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity' => 'nullable|integer|min:1|max:1000',
            'price' => 'required|numeric|min:0',
            'subscription_grace_days' => 'nullable|integer|min:0|max:30',
            'recurrence' => 'required|in:no,yes',
            'recurrence_frequency' => 'nullable|required_if:recurrence,yes|in:everyday,7days,monthly,yearly,custom',
            'until_date' => 'nullable|required_if:recurrence,yes|date|after_or_equal:date',
            'custom_frequency' => 'nullable|required_if:recurrence_frequency,custom|integer|min:1|max:365',
            'venue_name' => 'nullable|string|max:255',
        ]);

        $isSubscription = $validated['class_type'] === 'subscription';
        $frequency = $validated['class_type'] === 'single' ? null : ($validated['recurrence_frequency'] ?? null);

        if ($isSubscription && $frequency === 'custom') {
            throw ValidationException::withMessages([
                'recurrence_frequency' => 'Custom-day recurrence is not supported for subscription classes. Use daily, weekly, monthly, or yearly.',
            ]);
        }

        $billingInterval = $isSubscription ? $this->billingIntervalForFrequency($frequency) : null;

        return DB::transaction(function () use ($validated, $isSubscription, $frequency, $billingInterval) {
            $studioId = current_studio_id() ?: auth()->user()?->studio_id ?: 1;
            $isRecurring = $validated['class_type'] !== 'single';
            $start = Carbon::parse($validated['date'].' '.$validated['start_time']);
            $end = Carbon::parse($validated['date'].' '.$validated['end_time']);

            $class = ClassModel::create([
                'studio_id' => $studioId,
                'name' => $validated['class_name'],
                'description' => $validated['description'] ?? null,
                'teacher_id' => $validated['teacher_id'],
                'type' => $validated['class_type'],
                'is_recurring' => $isRecurring,
                'recurrence_frequency' => $frequency,
                'custom_frequency_days' => $frequency === 'custom' ? (int) $validated['custom_frequency'] : null,
                'until_date' => $isRecurring ? $validated['until_date'] : null,
                'capacity' => $validated['capacity'] ?? null,
                'price' => $validated['price'],
                'billing_interval' => $billingInterval,
                'subscription_grace_days' => $validated['subscription_grace_days'] ?? 3,
            ]);

            $sessions = [[
                'studio_id' => $studioId,
                'class_id' => $class->id,
                'start_time' => $start->copy(),
                'end_time' => $end->copy(),
                'capacity' => $validated['capacity'] ?? null,
                'venue_name' => $validated['venue_name'] ?? null,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now(),
            ]];

            if ($isRecurring) {
                $until = Carbon::parse($validated['until_date'])->endOfDay();
                $currentStart = $start->copy();
                $currentEnd = $end->copy();

                while (true) {
                    $this->advanceRecurringDates($currentStart, $currentEnd, $frequency, (int) ($validated['custom_frequency'] ?? 0));
                    if ($currentStart->gt($until)) {
                        break;
                    }

                    $sessions[] = [
                        'studio_id' => $studioId,
                        'class_id' => $class->id,
                        'start_time' => $currentStart->copy(),
                        'end_time' => $currentEnd->copy(),
                        'capacity' => $validated['capacity'] ?? null,
                        'venue_name' => $validated['venue_name'] ?? null,
                        'status' => 'scheduled',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            ClassSession::insert($sessions);

            return redirect()->route('admin.subscription-classes.show', $class->id)
                ->with('success', "Class created with ".count($sessions)." scheduled session(s).");
        });
    }

    public function destroy(Request $request, ClassSession $classSession)
    {
        $classSession->load('classModel');
        $hasActiveSubscriptions = $classSession->classModel->studioSubscriptions()
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->exists();

        if ($hasActiveSubscriptions) {
            $validated = $request->validate([
                'confirm_danger' => 'accepted',
                'change_reason' => 'required|string|min:10|max:1000',
            ]);

            $classSession->update([
                'status' => 'cancelled',
                'change_type' => 'cancelled',
                'change_reason' => $validated['change_reason'],
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            return back()->with('success', 'Session cancelled. The reason remains visible in the session history.');
        }

        $classSession->delete();

        return back()->with('success', 'Class session removed.');
    }

    private function assertSafeSubscriptionSequence(ClassSession $session, Carbon $newStart): void
    {
        $lastFulfilledAt = StudioSubscription::query()
            ->where('class_id', $session->class_id)
            ->whereNotNull('last_fulfilled_class_session_id')
            ->join('class_sessions as fulfilled', 'fulfilled.id', '=', 'studio_subscriptions.last_fulfilled_class_session_id')
            ->max('fulfilled.start_time');

        if ($lastFulfilledAt && $newStart->lte(Carbon::parse($lastFulfilledAt))) {
            throw ValidationException::withMessages(['date' => 'This session cannot be moved before or onto the last fulfilled subscription session.']);
        }

        $previous = ClassSession::where('class_id', $session->class_id)
            ->where('id', '!=', $session->id)
            ->where('start_time', '<', $session->start_time)
            ->where('status', '!=', 'cancelled')
            ->latest('start_time')
            ->first();

        $next = ClassSession::where('class_id', $session->class_id)
            ->where('id', '!=', $session->id)
            ->where('start_time', '>', $session->start_time)
            ->where('status', '!=', 'cancelled')
            ->oldest('start_time')
            ->first();

        if ($previous && $newStart->lte($previous->start_time)) {
            throw ValidationException::withMessages(['date' => 'The rescheduled date must remain after the previous session.']);
        }

        if ($next && $newStart->gte($next->start_time)) {
            throw ValidationException::withMessages(['date' => 'The rescheduled date must remain before the next session.']);
        }
    }

    private function billingIntervalForFrequency(?string $frequency): string
    {
        return match ($frequency) {
            'everyday' => 'day',
            '7days' => 'week',
            'monthly' => 'month',
            'yearly' => 'year',
            default => throw ValidationException::withMessages(['recurrence_frequency' => 'Choose a supported subscription frequency.']),
        };
    }

    private function advanceRecurringDates(Carbon $start, Carbon $end, ?string $frequency, int $customDays = 0): void
    {
        match ($frequency) {
            'everyday' => [$start->addDay(), $end->addDay()],
            '7days' => [$start->addWeek(), $end->addWeek()],
            'monthly' => [$start->addMonthNoOverflow(), $end->addMonthNoOverflow()],
            'yearly' => [$start->addYear(), $end->addYear()],
            'custom' => [$start->addDays(max(1, $customDays)), $end->addDays(max(1, $customDays))],
            default => null,
        };
    }
}