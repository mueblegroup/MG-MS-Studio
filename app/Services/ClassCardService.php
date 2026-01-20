<?php

namespace App\Services;

use App\Models\ClassCardUsage;
use App\Models\UserClassCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassCardService
{
    public function useCredit(int $userId, int $classSessionId): void
    {
        DB::transaction(function () use ($userId, $classSessionId) {

            $userCard = UserClassCard::query()
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->where('classes_remaining', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                })
                ->orderBy('expires_at')
                ->lockForUpdate()
                ->first();

            if (!$userCard) {
                throw ValidationException::withMessages([
                    'class_card' => 'No active class card with remaining credits.',
                ]);
            }

            $alreadyUsed = ClassCardUsage::query()
                ->where('user_class_card_id', $userCard->id)
                ->where('class_session_id', $classSessionId)
                ->exists();

            if ($alreadyUsed) {
                throw ValidationException::withMessages([
                    'class_card' => 'This class session was already paid using a class card.',
                ]);
            }

            ClassCardUsage::create([
                'user_class_card_id' => $userCard->id,
                'class_session_id' => $classSessionId,
                'used_at' => now(),
            ]);

            $userCard->decrement('classes_remaining');

            if ($userCard->fresh()->classes_remaining <= 0) {
                $userCard->update(['status' => 'expired']);
            }
        });
    }
}
