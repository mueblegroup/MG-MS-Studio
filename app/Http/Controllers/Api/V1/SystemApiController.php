<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ClassCard;
use App\Models\ClassModel;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemApiController extends BaseApiController
{
    public function me(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        return $this->success([
            'user' => $request->user()->only(['id', 'name', 'email', 'role', 'phone_number']),
            'token' => [
                'id' => $token?->id,
                'name' => $token?->name,
                'abilities' => $token?->abilities ?? [],
                'last_used_at' => $token?->last_used_at,
                'expires_at' => $token?->expires_at,
            ],
        ]);
    }

    public function dashboard(): JsonResponse
    {
        return $this->success([
            'users' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'students' => User::where('role', 'student')->count(),
            ],
            'commerce' => [
                'orders' => Order::count(),
                'paid_orders' => Order::where('status', 'paid')->count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
            ],
            'catalog' => [
                'classes' => ClassModel::count(),
                'plans' => Plan::count(),
                'classcards' => ClassCard::count(),
            ],
        ], 'Dashboard report loaded.');
    }
}
