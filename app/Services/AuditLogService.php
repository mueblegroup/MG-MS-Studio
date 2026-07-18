<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\TenantManager;
use Illuminate\Http\Request;
use Throwable;

class AuditLogService
{
    public function record(string $event, ?User $user = null, array $metadata = [], ?Request $request = null): void
    {
        try {
            $request ??= request();
            $user ??= $request->user();
            $studio = app(TenantManager::class)->current();

            AuditLog::query()->create([
                'user_id' => $user?->id,
                'studio_id' => $studio?->id ?? $user?->studio_id,
                'event' => $event,
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'metadata' => $this->sanitize($metadata),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function sanitize(array $metadata): array
    {
        foreach (['password', 'password_confirmation', 'current_password', 'code', 'recovery_code', '_token'] as $key) {
            unset($metadata[$key]);
        }

        return $metadata;
    }
}
