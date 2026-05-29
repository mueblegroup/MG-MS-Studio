<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ClassCardUsage;
use App\Models\ClassSessionAssignment;
use App\Models\UserClassCard;
use App\Services\ClassCardAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceApiController extends BaseApiController
{
    public function classCardUsages(Request $request): JsonResponse
    {
        $query = ClassCardUsage::query()->with(['userClassCard.user', 'userClassCard.classCard'])->latest('used_at');

        if ($request->filled('user_class_card_id')) {
            $query->where('user_class_card_id', $request->integer('user_class_card_id'));
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Class card usages loaded.');
    }

    public function markClassCard(Request $request, UserClassCard $userClassCard, ClassCardAttendanceService $service): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $service->markUsed($userClassCard->id, $request->user()->id, $validated['notes'] ?? null);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            $userClassCard->fresh(['user', 'classCard', 'usages']),
            'Class card attendance marked.'
        );
    }

    public function classAssignments(Request $request): JsonResponse
    {
        $query = ClassSessionAssignment::query()->with(['user', 'classSession.classModel'])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('class_session_id')) {
            $query->where('class_session_id', $request->integer('class_session_id'));
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Class assignments loaded.');
    }
}
