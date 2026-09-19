<?php

namespace App\Models\Concerns;

trait InvalidatesAttendanceQr
{
    protected static function bootInvalidatesAttendanceQr(): void
    {
        static::updating(function ($session) {
            if ($session->isDirty(['start_time', 'end_time', 'status', 'class_id', 'plan_id', 'studio_id'])) {
                $session->attendance_qr_version = null;
            }
        });
    }
}
