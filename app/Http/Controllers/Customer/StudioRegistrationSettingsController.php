<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Studio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudioRegistrationSettingsController extends Controller
{
    public function update(Request $request, Studio $studio): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user->role === 'admin' && (int) $studio->owner_user_id === (int) $user->id,
            403,
            'Only the studio owner can change student registration settings.'
        );

        $settings = $studio->settings ?? [];
        $settings['allow_student_self_registration'] = $request->boolean('allow_student_self_registration');

        $studio->update(['settings' => $settings]);

        return back()->with('success', 'Student self-registration setting updated.');
    }
}
