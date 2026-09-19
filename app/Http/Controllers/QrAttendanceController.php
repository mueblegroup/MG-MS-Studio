<?php

namespace App\Http\Controllers;

use App\Services\QrAttendanceService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class QrAttendanceController extends Controller
{
    public function show(Request $request, QrAttendanceService $service, string $kind, int $id)
    {
        $session = $service->session($kind, $id);
        $service->authorizeStaff($request->user(), $session);
        $url = $service->checkInUrl($kind, $session);
        $data = [
            'url' => $url,
            'can_open' => $service->windowMessage($session) === null,
            'message' => $service->windowMessage($session) ?? ($url ? 'Check-in is open.' : 'Select Open QR check-in to display the code.'),
            'remaining' => $url ? max(0, (int) now()->diffInSeconds($session->end_time, false)) : 0,
            'attendees' => $service->attendees($session),
        ];
        if ($request->expectsJson()) {
            return response()->json($data)->withHeaders($this->privateHeaders());
        }

        $role = $request->user()->role;
        $backUrl = $kind === 'class'
            ? route($role.'.classes.attendance'.($role === 'teacher' ? '.show' : ''), $id)
            : route($role.'.plans.sessions.attendance'.($role === 'teacher' ? '.show' : ''), [$session->plan_id, $id]);

        return response()->view('attendance.qr', compact('session', 'kind', 'data', 'backUrl'))
            ->withHeaders($this->privateHeaders());
    }

    public function open(Request $request, QrAttendanceService $service, string $kind, int $id)
    {
        $request->validate(['replace' => ['sometimes', 'boolean']]);
        $service->openQr($request->user(), $kind, $id, $request->boolean('replace'));

        return redirect()->route('attendance.qr.show', [$kind, $id]);
    }

    public function checkIn(Request $request, QrAttendanceService $service, string $kind, int $id)
    {
        try {
            $session = $service->session($kind, $id);
            $service->validateScan($request, $session);
            $service->attendanceKey($request->user(), $session);
            $attendance = $service->existingAttendance($request->user(), $session);
            abort_if($attendance && $attendance->status !== 'attended', 409,
                'Attendance was already recorded by staff. Ask your teacher to correct it.');

            return response()->view('attendance.check-in', [
                'session' => $session, 'alreadyAttended' => $attendance?->status === 'attended',
                'submitUrl' => $request->fullUrl(), 'error' => null,
            ])->withHeaders($this->privateHeaders());
        } catch (HttpExceptionInterface $e) {
            return $this->checkInError($e);
        }
    }

    public function confirm(Request $request, QrAttendanceService $service, string $kind, int $id)
    {
        try {
            $created = $service->confirm($request, $kind, $id);

            // The GET is read-only; refreshing after confirmation cannot repeat a write.
            return redirect()->to($request->fullUrl())->with('success', $created
                ? 'You are checked in. Your attendance has been recorded.'
                : 'You are already checked in. No changes were made.');
        } catch (HttpExceptionInterface $e) {
            return $this->checkInError($e);
        }
    }

    private function checkInError(HttpExceptionInterface $e)
    {
        return response()->view('attendance.check-in', [
            'session' => null, 'alreadyAttended' => false, 'submitUrl' => null,
            'error' => $e->getMessage() ?: 'This session is no longer available. Contact your teacher.',
        ], $e->getStatusCode())->withHeaders($this->privateHeaders());
    }

    private function privateHeaders(): array
    {
        return ['Cache-Control' => 'no-store, private', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow'];
    }
}
