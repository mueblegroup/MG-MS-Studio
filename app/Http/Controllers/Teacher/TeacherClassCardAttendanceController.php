<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\UserClassCard;
use App\Services\ClassCardAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherClassCardAttendanceController extends Controller
{
    public function mark(Request $request, ClassCardAttendanceService $svc, int $userClassCard)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        $teacherId = Auth::id();

        $ucc = UserClassCard::query()->findOrFail($userClassCard);

        // Safety checks (same logic as admin disable)
        if (($ucc->status ?? 'active') !== 'active') {
            return back()->with('error', 'This class card is not active.');
        }

        if (($ucc->classes_remaining ?? 0) <= 0) {
            return back()->with('error', 'No classes remaining on this card.');
        }

        if ($ucc->expires_at && now()->gt($ucc->expires_at)) {
            return back()->with('error', 'This class card is expired.');
        }

        try {
            // used_by = teacher id
            $svc->markUsed($ucc->id, $teacherId, $validated['notes'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Marked attendance. 1 class deducted.');
    }
}