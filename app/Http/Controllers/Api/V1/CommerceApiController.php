<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ClassCard;
use App\Models\ClassModel;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommerceApiController extends BaseApiController
{
    public function payments(Request $request): JsonResponse
    {
        $query = Payment::query()->with(['user', 'order'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Payments loaded.');
    }

    public function payment(Payment $payment): JsonResponse
    {
        return $this->success($payment->load(['user', 'order.items']), 'Payment loaded.');
    }

    public function orders(Request $request): JsonResponse
    {
        $query = Order::query()->with(['user', 'items'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Orders loaded.');
    }

    public function order(Order $order): JsonResponse
    {
        return $this->success($order->load(['user', 'items']), 'Order loaded.');
    }

    public function shop(): JsonResponse
    {
        return $this->success([
            'classes' => ClassModel::query()->with('sessions')->latest()->get(),
            'plans' => Plan::query()->with('sessions')->where('is_active', true)->latest()->get(),
            'classcards' => ClassCard::query()->where('is_active', true)->latest()->get(),
        ], 'Shop catalog loaded.');
    }
}
