<?php

namespace App\Http\Controllers;

use App\Support\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $start = Carbon::parse($validated['start']);
        $end = Carbon::parse($validated['end']);
        $user = $request->user();
        $studioId = (int) (app(TenantManager::class)->id() ?: $user?->studio_id);

        abort_unless($user && $studioId && in_array($user->role, ['admin', 'teacher', 'student'], true), 403);

        $classSessions = DB::table('class_sessions as sessions')
            ->join('classes', 'sessions.class_id', '=', 'classes.id')
            ->leftJoin('users as teachers', 'classes.teacher_id', '=', 'teachers.id')
            ->where('classes.studio_id', $studioId)
            ->whereBetween('sessions.start_time', [$start, $end])
            ->where(function ($query) {
                $query->whereNull('sessions.status')
                    ->orWhere('sessions.status', '!=', 'cancelled');
            });

        $planSessions = DB::table('plan_sessions as sessions')
            ->join('plans', 'sessions.plan_id', '=', 'plans.id')
            ->leftJoin('users as teachers', 'plans.teacher_id', '=', 'teachers.id')
            ->where('plans.studio_id', $studioId)
            ->where('plans.is_active', true)
            ->whereNull('plans.deleted_at')
            ->whereNull('sessions.deleted_at')
            ->whereBetween('sessions.start_time', [$start, $end]);

        if ($user->role === 'teacher') {
            $classSessions->where('classes.teacher_id', $user->id);
            $planSessions->where('plans.teacher_id', $user->id);
        }

        if ($user->role === 'student') {
            $classSessions->where(function ($query) use ($user) {
                $query->whereExists(function ($assignment) use ($user) {
                    $assignment->selectRaw('1')
                        ->from('class_session_assignments as assignments')
                        ->whereColumn('assignments.class_session_id', 'sessions.id')
                        ->where('assignments.user_id', $user->id)
                        ->whereNull('assignments.deleted_at')
                        ->where(function ($status) {
                            $status->whereNull('assignments.status')
                                ->orWhereNotIn('assignments.status', ['cancelled', 'inactive']);
                        });
                })->orWhereExists(function ($subscription) use ($user) {
                    $subscription->selectRaw('1')
                        ->from('studio_subscriptions as subscriptions')
                        ->whereColumn('subscriptions.class_id', 'classes.id')
                        ->where('subscriptions.user_id', $user->id)
                        ->whereIn('subscriptions.status', ['active', 'trialing', 'past_due'])
                        ->whereNull('subscriptions.cancelled_at');
                });
            });

            $planSessions->whereExists(function ($membership) use ($user) {
                $membership->selectRaw('1')
                    ->from('user_plans as memberships')
                    ->whereColumn('memberships.plan_id', 'plans.id')
                    ->where('memberships.user_id', $user->id)
                    ->where('memberships.is_active', true)
                    ->where(function ($window) {
                        $window->whereNull('memberships.starts_on')
                            ->orWhereColumn('sessions.start_time', '>=', 'memberships.starts_on');
                    })
                    ->where(function ($window) {
                        $window->whereNull('memberships.ends_on')
                            ->orWhereColumn('sessions.start_time', '<=', 'memberships.ends_on');
                    });
            });
        }

        $classRows = $classSessions
            ->orderBy('sessions.start_time')
            ->get([
                'sessions.id',
                'sessions.start_time',
                'sessions.end_time',
                'sessions.venue_name',
                'classes.name',
                'classes.description',
                'classes.type',
                'teachers.name as teacher_name',
            ]);

        $confirmedSubscriptionSessionIds = collect();
        if ($user->role === 'student') {
            $confirmedSubscriptionSessionIds = DB::table('class_session_assignments')
                ->where('user_id', $user->id)
                ->whereIn('class_session_id', $classRows->pluck('id'))
                ->whereNull('deleted_at')
                ->where(function ($status) {
                    $status->whereNull('status')
                        ->orWhereNotIn('status', ['cancelled', 'inactive']);
                })
                ->pluck('class_session_id')
                ->map(fn ($id) => (int) $id);
        }

        $classEvents = $classRows->map(function ($session) use ($user, $confirmedSubscriptionSessionIds) {
            $isSubscription = $session->type === 'subscription';
            $isConfirmed = ! $isSubscription
                || $user->role !== 'student'
                || $confirmedSubscriptionSessionIds->contains((int) $session->id);

            $billingStatus = null;
            $billingMessage = null;
            if ($isSubscription && $user->role === 'student') {
                $billingStatus = $isConfirmed ? 'confirmed' : 'awaiting_charge';
                $billingMessage = $isConfirmed
                    ? 'Confirmed — payment received and this session is available for attendance.'
                    : 'Awaiting recurring charge — this session will be confirmed after its payment succeeds.';
            }

            return [
                'id' => 'class-'.$session->id,
                'title' => $session->name.($session->venue_name ? ' • '.$session->venue_name : ''),
                'start' => $session->start_time,
                'end' => $session->end_time,
                'backgroundColor' => $isSubscription
                    ? ($isConfirmed ? '#7c3aed' : '#d97706')
                    : '#4f46e5',
                'borderColor' => 'transparent',
                'extendedProps' => [
                    'kind' => $isSubscription ? 'subscription' : 'class',
                    'name' => $session->name,
                    'description' => $session->description,
                    'teacher' => $session->teacher_name,
                    'venue' => $session->venue_name,
                    'billingStatus' => $billingStatus,
                    'billingMessage' => $billingMessage,
                ],
            ];
        });

        $planEvents = $planSessions
            ->orderBy('sessions.start_time')
            ->get([
                'sessions.id',
                'sessions.session_name',
                'sessions.start_time',
                'sessions.end_time',
                'sessions.venue_name',
                'plans.name',
                'plans.description',
                'teachers.name as teacher_name',
            ])
            ->map(fn ($session) => [
                'id' => 'plan-'.$session->id,
                'title' => ($session->session_name ?: $session->name).($session->venue_name ? ' • '.$session->venue_name : ''),
                'start' => $session->start_time,
                'end' => $session->end_time,
                'backgroundColor' => '#2563eb',
                'borderColor' => 'transparent',
                'extendedProps' => [
                    'kind' => 'plan',
                    'name' => $session->session_name ?: $session->name,
                    'description' => $session->description,
                    'teacher' => $session->teacher_name,
                    'venue' => $session->venue_name,
                ],
            ]);

        return response()->json($classEvents->merge($planEvents)->sortBy('start')->values());
    }
}
