<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PlanSessionController extends Controller
{
    /**
     * Show edit form for a single session inside a plan.
     */
    public function edit(Plan $plan, PlanSession $session)
    {
        $this->assertBelongsToPlan($plan, $session);

        // If you want to include soft-deleted sessions in edit, use withTrashed() in routes/controller.
        return view('admin.plans.sessions.edit', [
            'plan' => $plan,
            'session' => $session,
        ]);
    }

    /**
     * Update a single plan session.
     */
    public function update(Request $request, Plan $plan, PlanSession $session)
    {
        $this->assertBelongsToPlan($plan, $session);

        $validated = $request->validate([
            'session_name' => ['nullable', 'string', 'max:255'],
            'date'         => ['required', 'date'],
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i', 'after:start_time'],
            'capacity'     => ['nullable', 'integer', 'min:1', 'max:10000'],
            'venue_name'   => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($validated, $plan, $session) {
            $start = Carbon::parse($validated['date'] . ' ' . $validated['start_time'] . ':00');
            $end   = Carbon::parse($validated['date'] . ' ' . $validated['end_time'] . ':00');

            $session->update([
                'session_name' => $validated['session_name'] ?? null,
                'start_time'   => $start,
                'end_time'     => $end,
                'capacity'     => $validated['capacity'] ?? null,
                'venue_name'   => $validated['venue_name'] ?? null,
            ]);

            return redirect()
                ->route('admin.plans.show', $plan)
                ->with('success', 'Session updated successfully.');
        });
    }

    /**
     * Delete (soft delete) a single plan session.
     */
    public function destroy(Plan $plan, PlanSession $session)
    {
        $this->assertBelongsToPlan($plan, $session);

        // SoftDeletes on PlanSession => this will soft delete
        $session->delete();

        return redirect()
            ->route('admin.plans.show', $plan)
            ->with('success', 'Session removed.');
    }

    /**
     * Safety: prevent editing/deleting sessions from the wrong plan.
     */
    private function assertBelongsToPlan(Plan $plan, PlanSession $session): void
    {
        if ((int) $session->plan_id !== (int) $plan->id) {
            abort(404);
        }
    }
}
