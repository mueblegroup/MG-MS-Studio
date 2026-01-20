<?php

namespace App\Http\Controllers;

use App\Services\ClassCardAttendanceService;
use Illuminate\Http\Request;

class ClassCardAttendanceController extends Controller
{
    public function mark(Request $request, ClassCardAttendanceService $svc, int $userClassCardId)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $svc->markUsed($userClassCardId, auth()->id(), $validated['notes'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Marked attendance. 1 class deducted.');
    }
}
