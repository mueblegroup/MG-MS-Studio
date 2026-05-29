<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ClassCard;
use App\Models\UserClassCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassCardApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = ClassCard::query()->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('name', 'like', "%{$search}%");
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Class cards loaded.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'total_classes' => ['required', 'integer', 'min:1'],
            'validity_weeks' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $card = ClassCard::create($validated);

        return $this->success($card, 'Class card created.', 201);
    }

    public function show(ClassCard $classcard): JsonResponse
    {
        return $this->success($classcard->load('userClassCards.user'), 'Class card loaded.');
    }

    public function update(Request $request, ClassCard $classcard): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'total_classes' => ['sometimes', 'required', 'integer', 'min:1'],
            'validity_weeks' => ['sometimes', 'required', 'integer', 'min:1'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $classcard->update($validated);

        return $this->success($classcard->fresh(), 'Class card updated.');
    }

    public function destroy(ClassCard $classcard): JsonResponse
    {
        $classcard->delete();

        return $this->success(null, 'Class card deleted.');
    }

    public function purchases(Request $request): JsonResponse
    {
        $query = UserClassCard::query()->with(['user', 'classCard'])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Class card purchases loaded.');
    }

    public function storePurchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'class_card_id' => ['required', 'exists:class_cards,id'],
            'purchased_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'classes_remaining' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $card = ClassCard::findOrFail($validated['class_card_id']);
        $validated['purchased_at'] = $validated['purchased_at'] ?? now();
        $validated['expires_at'] = $validated['expires_at'] ?? now()->addWeeks((int) $card->validity_weeks);
        $validated['classes_remaining'] = $validated['classes_remaining'] ?? (int) $card->total_classes;
        $validated['status'] = $validated['status'] ?? 'active';

        $purchase = UserClassCard::create($validated);

        return $this->success($purchase->load(['user', 'classCard']), 'Class card purchase created.', 201);
    }

    public function showPurchase(UserClassCard $purchase): JsonResponse
    {
        return $this->success($purchase->load(['user', 'classCard', 'usages']), 'Class card purchase loaded.');
    }

    public function updatePurchase(Request $request, UserClassCard $purchase): JsonResponse
    {
        $validated = $request->validate([
            'purchased_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'classes_remaining' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $purchase->update($validated);

        return $this->success($purchase->fresh(['user', 'classCard']), 'Class card purchase updated.');
    }

    public function destroyPurchase(UserClassCard $purchase): JsonResponse
    {
        $purchase->delete();

        return $this->success(null, 'Class card purchase deleted.');
    }
}
