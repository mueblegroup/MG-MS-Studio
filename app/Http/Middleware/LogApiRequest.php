<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $user = $request->user();
            $token = $user?->currentAccessToken();

            ApiRequestLog::create([
                'user_id' => $user?->id,
                'token_id' => $token?->id,
                'token_name' => $token?->name,
                'method' => $request->method(),
                'endpoint' => '/' . ltrim($request->path(), '/'),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                'status_code' => $response->getStatusCode(),
                'request_payload' => $this->safePayload($request),
                'response_summary' => $this->responseSummary($response),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }

    protected function safePayload(Request $request): array
    {
        return Arr::except($request->all(), [
            'password',
            'password_confirmation',
            'token',
            'api_token',
            'authorization',
            '_token',
        ]);
    }

    protected function responseSummary(Response $response): array
    {
        $summary = [
            'status' => $response->getStatusCode(),
        ];

        $content = method_exists($response, 'getContent') ? (string) $response->getContent() : '';
        if ($content !== '') {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $summary['success'] = $decoded['success'] ?? null;
                $summary['message'] = $decoded['message'] ?? null;
                $summary['keys'] = is_array($decoded) ? array_slice(array_keys($decoded), 0, 20) : [];
            }
        }

        return $summary;
    }
}
