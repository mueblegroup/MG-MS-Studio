<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ApiRequestLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiLogController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = ApiRequestLog::query()->with('user')->latest();

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->integer('status_code'));
        }

        if ($request->filled('token_id')) {
            $query->where('token_id', $request->integer('token_id'));
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'API logs loaded.');
    }
}
