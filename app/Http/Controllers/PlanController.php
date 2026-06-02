<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10,25,50,100]) ? $perPage : 10;

        $plans = Plan::query()
            ->withCount('sessions')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.plans.index', compact('plans', 'search'));
    }

    public function show(Plan $plan)
    {
        $plan->load([
            'sessions' => function ($q) {
                $q->orderBy('start_time');
            }
        ]);

        return view('admin.plans.show', compact('plan'));
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')->get();
        return view('admin.plans.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // plan details
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'teacher_id' => 'nullable|exists:users,id',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',

            // first session
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',

            'capacity' => 'nullable|integer|min:1|max:1000',
            'venue_name' => 'nullable|string|max:255',
            'session_name' => 'nullable|string|max:255',

            // recurrence
            'recurrence' => 'required|in:no,yes',
            'recurrence_frequency' => 'nullable|required_if:recurrence,yes|in:everyday,7days,monthly,yearly,custom',
            'until_date' => 'nullable|required_if:recurrence,yes|date|after_or_equal:date',
            'custom_frequency' => 'nullable|required_if:recurrence_frequency,custom|integer|min:1|max:365',
        ]);

        return DB::transaction(function () use ($validated) {

            $studioId = current_studio_id() ?: auth()->user()?->studio_id ?: 1;
            $isRecurring = $validated['recurrence'] === 'yes';

            $plan = Plan::create([
                'studio_id' => $studioId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'teacher_id' => $validated['teacher_id'] ?? null,
                'price' => $validated['price'],
                'currency' => strtoupper($validated['currency'] ?? 'MYR'),

                'is_recurring' => $isRecurring,
                'recurrence_frequency' => $isRecurring ? $validated['recurrence_frequency'] : null,
                'custom_frequency_days' => ($isRecurring && ($validated['recurrence_frequency'] ?? null) === 'custom')
                    ? (int) $validated['custom_frequency']
                    : null,
                'until_date' => $isRecurring ? $validated['until_date'] : null,
            ]);

            // first session
            $start = Carbon::parse($validated['date'].' '.$validated['start_time']);
            $end   = Carbon::parse($validated['date'].' '.$validated['end_time']);

            PlanSession::create([
                'studio_id' => $studioId,
                'plan_id' => $plan->id,
                'session_name' => $validated['session_name'] ?? null,
                'start_time' => $start,
                'end_time' => $end,
                'capacity' => $validated['capacity'] ?? null,
                'venue_name' => $validated['venue_name'] ?? null,
            ]);

            // generate additional sessions
            if ($isRecurring) {
                $until = Carbon::parse($validated['until_date'])->endOfDay();

                $currentStart = $start->copy();
                $currentEnd   = $end->copy();

                while (true) {
                    switch ($validated['recurrence_frequency']) {
                        case 'everyday':
                            $currentStart->addDay();
                            $currentEnd->addDay();
                            break;

                        case '7days':
                            $currentStart->addDays(7);
                            $currentEnd->addDays(7);
                            break;

                        case 'monthly':
                            $currentStart->addMonth();
                            $currentEnd->addMonth();
                            break;

                        case 'yearly':
                            $currentStart->addYear();
                            $currentEnd->addYear();
                            break;

                        case 'custom':
                            $days = (int) $validated['custom_frequency'];
                            $currentStart->addDays($days);
                            $currentEnd->addDays($days);
                            break;
                    }

                    if ($currentStart->gt($until)) {
                        break;
                    }

                    PlanSession::create([
                        'studio_id' => $studioId,
                        'plan_id' => $plan->id,
                        'session_name' => $validated['session_name'] ?? null,
                        'start_time' => $currentStart->copy(),
                        'end_time' => $currentEnd->copy(),
                        'capacity' => $validated['capacity'] ?? null,
                        'venue_name' => $validated['venue_name'] ?? null,
                    ]);
                }
            }

            return redirect()
                ->route('admin.plans')
                ->with('success', 'Plan created successfully.');
        });
    }

 public function edit(Plan $plan)
{
    // if you want to edit sessions on this page later:
    $teachers = User::where('role', 'teacher')->get();
    $plan->load(['sessions' => fn($q) => $q->orderBy('start_time')]);

    return view('admin.plans.edit', compact('plan', 'teachers'));
}

public function update(Request $request, Plan $plan)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:5000',
        'teacher_id' => 'nullable|exists:users,id',
        'price' => 'required|numeric|min:0',
        'currency' => 'nullable|string|size:3',

        'is_recurring' => 'required|in:no,yes',
        'recurrence_frequency' => 'nullable|required_if:is_recurring,yes|in:everyday,7days,monthly,yearly,custom',
        'until_date' => 'nullable|required_if:is_recurring,yes|date|after_or_equal:today',
        'custom_frequency' => 'nullable|required_if:recurrence_frequency,custom|integer|min:1|max:365',
    ]);

    $isRecurring = $validated['is_recurring'] === 'yes';
    $freq = $isRecurring ? ($validated['recurrence_frequency'] ?? null) : null;
    $customDays = ($isRecurring && $freq === 'custom') ? (int)($validated['custom_frequency'] ?? 0) : null;
    $untilDate = $isRecurring ? Carbon::parse($validated['until_date'])->endOfDay() : null;

    // Detect schedule change (so we only regenerate when needed)
    $scheduleChanged =
        (bool)$plan->is_recurring !== $isRecurring ||
        ($plan->recurrence_frequency ?? null) !== ($freq ?? null) ||
        (int)($plan->custom_frequency_days ?? 0) !== (int)($customDays ?? 0) ||
        optional($plan->until_date)->format('Y-m-d') !== ($isRecurring ? Carbon::parse($validated['until_date'])->format('Y-m-d') : null);

    DB::transaction(function () use ($plan, $validated, $isRecurring, $freq, $customDays, $untilDate, $scheduleChanged) {

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'teacher_id' => $validated['teacher_id'] ?? null,
            'price' => $validated['price'],
            'currency' => strtoupper($validated['currency'] ?? ($plan->currency ?? 'MYR')),

            'is_recurring' => $isRecurring,
            'recurrence_frequency' => $freq,
            'custom_frequency_days' => $customDays,
            'until_date' => $untilDate?->toDateString(),
        ]);

        if (!$scheduleChanged) {
            return;
        }

        // Only regenerate FUTURE sessions
        $today = Carbon::today();

        // Find an anchor session (first session) to preserve time-of-day pattern
        $first = $plan->sessions()
            ->orderBy('start_time')
            ->first();

        if (!$first) {
            // No sessions exist; nothing to regenerate
            return;
        }

        $startTimeOfDay = Carbon::parse($first->start_time);
        $endTimeOfDay = Carbon::parse($first->end_time);

        // Delete future sessions (soft delete)
        $plan->sessions()
            ->whereDate('start_time', '>=', $today->toDateString())
            ->delete();

        // If not recurring anymore, stop here
        if (!$isRecurring) {
            return;
        }

        // Regenerate starting from next valid date >= today
        $cursorStart = $startTimeOfDay->copy();
        $cursorEnd   = $endTimeOfDay->copy();

        // Ensure cursor starts at >= today date (but keep time)
        if ($cursorStart->lt($today)) {
            $cursorStart = $today->copy()->setTimeFrom($startTimeOfDay);
            $cursorEnd   = $today->copy()->setTimeFrom($endTimeOfDay);
        }

        // Generate sessions until until_date
        while ($cursorStart->lte($untilDate)) {

            $plan->sessions()->create([
                'studio_id' => $plan->studio_id ?: current_studio_id() ?: auth()->user()?->studio_id ?: 1,
                'session_name' => $first->session_name,
                'start_time' => $cursorStart->copy(),
                'end_time' => $cursorEnd->copy(),
                'capacity' => $first->capacity,
                'venue_name' => $first->venue_name,
            ]);

            // advance
            switch ($freq) {
                case 'everyday':
                    $cursorStart->addDay();
                    $cursorEnd->addDay();
                    break;
                case '7days':
                    $cursorStart->addDays(7);
                    $cursorEnd->addDays(7);
                    break;
                case 'monthly':
                    $cursorStart->addMonth();
                    $cursorEnd->addMonth();
                    break;
                case 'yearly':
                    $cursorStart->addYear();
                    $cursorEnd->addYear();
                    break;
                case 'custom':
                    $cursorStart->addDays($customDays);
                    $cursorEnd->addDays($customDays);
                    break;
            }
        }
    });

    return redirect()
        ->route('admin.plans.show', $plan->id)
        ->with('success', 'Plan updated. Future sessions were regenerated.');
}

public function destroy(Plan $plan)
{
    DB::transaction(function () use ($plan) {
        // Optional: also soft delete sessions
        $plan->sessions()->delete();
        $plan->delete();
    });

    return redirect()
        ->route('admin.plans')
        ->with('success', 'Plan deleted successfully.');
}

/* Plan Session */
public function editSession(Plan $plan, PlanSession $session)
{
    abort_unless($session->plan_id === $plan->id, 404);

    return view('admin.plans.sessions.edit', compact('plan', 'session'));
}

public function updateSession(Request $request, Plan $plan, PlanSession $session)
{
    abort_unless($session->plan_id === $plan->id, 404);

    $validated = $request->validate([
        'session_name' => 'nullable|string|max:255',
        'date' => 'required|date',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'capacity' => 'nullable|integer|min:1|max:1000',
        'venue_name' => 'nullable|string|max:255',
    ]);

    $start = Carbon::parse($validated['date'].' '.$validated['start_time']);
    $end = Carbon::parse($validated['date'].' '.$validated['end_time']);

    $session->update([
        'session_name' => $validated['session_name'] ?? null,
        'start_time' => $start,
        'end_time' => $end,
        'capacity' => $validated['capacity'] ?? null,
        'venue_name' => $validated['venue_name'] ?? null,
    ]);

    return redirect()
        ->route('admin.plans.show', $plan->id)
        ->with('success', 'Session updated.');
}

public function destroySession(Plan $plan, PlanSession $session)
{
    abort_unless($session->plan_id === $plan->id, 404);

    $session->delete();

    return redirect()
        ->route('admin.plans.show', $plan->id)
        ->with('success', 'Session removed.');
}
}