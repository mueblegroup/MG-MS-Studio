<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);
        $sessions = ClassSession::query()
            ->with(['classModel.teacher:id,name,email'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('classModel', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('teacher', function ($t) use ($search) {
                          $t->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            })
            ->orderByDesc('start_time')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.classes.index', compact('sessions', 'search', 'perPage'));
    }

    public function data()
    {
        $rows = ClassSession::query()
            ->with(['classModel.teacher:id,name,email'])
            ->orderByDesc('start_time')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'class_name' => $s->classModel->name ?? '-',
                    'description' => $s->classModel->description ?? '',
                    'teacher_email' => $s->classModel->teacher->email ?? '-',
                    'start_time' => optional($s->start_time)->format('H:i'),
                    'end_time' => optional($s->end_time)->format('H:i'),
                    'date' => optional($s->start_time)->format('Y-m-d'),
                    'capacity' => $s->capacity ?? $s->classModel->capacity ?? null,
                    'price' => $s->classModel->price ?? 0,
                    'type' => $s->classModel->type ?? 'single',
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.classes.create', compact('teachers'));
    }

    public function edit(ClassSession $classSession)
    {
        $classSession->load(['classModel.teacher:id,name,email']);

        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get(['id','name','email']);

        return view('admin.classes.edit', [
            'session' => $classSession,
            'class' => $classSession->classModel,
            'teachers' => $teachers,
        ]);
    }

    public function update(Request $request, ClassSession $classSession)
    {
        $classSession->load('classModel');

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
        ]);

        return DB::transaction(function () use ($validated, $classSession) {
            $classSession->classModel->update([
                'name' => $validated['class_name'],
                'teacher_id' => $validated['teacher_id'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
                'capacity' => $validated['capacity'] ?? null,
                'type' => $validated['class_type'],
                'billing_interval' => $validated['class_type'] === 'subscription' ? $validated['billing_interval'] : null,
                'subscription_grace_days' => $validated['subscription_grace_days'] ?? 3,
            ]);

            $start = Carbon::parse($validated['date'].' '.$validated['start_time']);
            $end   = Carbon::parse($validated['date'].' '.$validated['end_time']);

            $classSession->update([
                'start_time' => $start,
                'end_time' => $end,
                'capacity' => $validated['capacity'] ?? null,
                'venue_name' => $validated['venue_name'] ?? null,
            ]);

            return redirect()->route('admin.classes')->with('success', 'Class updated successfully.');
        });
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
            'billing_interval' => 'nullable|required_if:class_type,subscription|in:day,week,month,year',
            'subscription_grace_days' => 'nullable|integer|min:0|max:30',
            'recurrence' => 'required|in:no,yes',
            'recurrence_frequency' => 'nullable|required_if:recurrence,yes|in:everyday,7days,monthly,yearly,custom',
            'until_date' => 'nullable|required_if:recurrence,yes|date|after_or_equal:date',
            'custom_frequency' => 'nullable|required_if:recurrence_frequency,custom|integer|min:1|max:365',
            'venue_name' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $isSubscription = $validated['class_type'] === 'subscription';
            $isRecurring = $isSubscription || $validated['class_type'] === 'recurring' || $validated['recurrence'] === 'yes';
            $frequency = $isRecurring ? ($validated['recurrence_frequency'] ?? 'monthly') : null;

            $start = Carbon::parse($validated['date'].' '.$validated['start_time']);
            $end   = Carbon::parse($validated['date'].' '.$validated['end_time']);

            $class = ClassModel::create([
                'name' => $validated['class_name'],
                'description' => $validated['description'] ?? null,
                'teacher_id' => $validated['teacher_id'],
                'type' => $validated['class_type'],
                'is_recurring' => $isRecurring,
                'recurrence_frequency' => $frequency,
                'custom_frequency_days' => ($frequency === 'custom') ? (int)$validated['custom_frequency'] : null,
                'until_date' => $isRecurring ? ($validated['until_date'] ?? null) : null,
                'capacity' => $validated['capacity'] ?? null,
                'price' => $validated['price'],
                'billing_interval' => $isSubscription ? $validated['billing_interval'] : null,
                'subscription_grace_days' => $validated['subscription_grace_days'] ?? 3,
            ]);

            $sessionsToCreate = [[
                'class_id' => $class->id,
                'start_time' => $start->copy(),
                'end_time' => $end->copy(),
                'capacity' => $validated['capacity'] ?? null,
                'venue_name' => $validated['venue_name'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]];

            if ($isRecurring && !empty($validated['until_date'])) {
                $until = Carbon::parse($validated['until_date'])->endOfDay();
                $currentStart = $start->copy();
                $currentEnd   = $end->copy();

                while (true) {
                    $this->advanceRecurringDates($currentStart, $currentEnd, $frequency, (int)($validated['custom_frequency'] ?? 0));

                    if ($currentStart->gt($until)) {
                        break;
                    }

                    $sessionsToCreate[] = [
                        'class_id' => $class->id,
                        'start_time' => $currentStart->copy(),
                        'end_time' => $currentEnd->copy(),
                        'capacity' => $validated['capacity'] ?? null,
                        'venue_name' => $validated['venue_name'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            ClassSession::insert($sessionsToCreate);

            return redirect()->route('admin.classes')->with('success', 'Class created successfully.');
        });
    }

    private function advanceRecurringDates(Carbon $start, Carbon $end, ?string $frequency, int $customDays = 0): void
    {
        switch ($frequency) {
            case 'everyday':
                $start->addDay();
                $end->addDay();
                break;
            case '7days':
                $start->addDays(7);
                $end->addDays(7);
                break;
            case 'monthly':
                $start->addMonth();
                $end->addMonth();
                break;
            case 'yearly':
                $start->addYear();
                $end->addYear();
                break;
            case 'custom':
                $days = max(1, $customDays);
                $start->addDays($days);
                $end->addDays($days);
                break;
            default:
                break;
        }
    }

    public function destroy(ClassSession $classSession)
    {
        $classSession->delete();
        return redirect()->route('admin.classes')->with('success', 'Class session removed.');
    }
}
