<?php

namespace App\Services;

use App\Models\ClassCardUsage;
use App\Models\UserClassCard;
use Illuminate\Support\Facades\DB;

class ClassCardAttendanceService
{
    public function markUsed(int $userClassCardId, ?int $markedByUserId = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($userClassCardId, $markedByUserId, $notes) {

            /** @var UserClassCard $ucc */
            $ucc = UserClassCard::lockForUpdate()->findOrFail($userClassCardId);

            // Basic validations
            if (($ucc->status ?? 'active') !== 'active') {
                throw new \RuntimeException('This class card is not active.');
            }

            if ($ucc->expires_at && now()->greaterThan($ucc->expires_at)) {
                // optional: auto-mark expired
                $ucc->update(['status' => 'expired']);
                throw new \RuntimeException('This class card is expired.');
            }

            if ((int)$ucc->classes_remaining <= 0) {
                throw new \RuntimeException('No remaining classes on this card.');
            }

            // Decrement
            $ucc->update([
                'classes_remaining' => (int)$ucc->classes_remaining - 1,
            ]);

            // Log usage (recommended)
            ClassCardUsage::create([
                'user_class_card_id' => $ucc->id,
                'used_by' => $markedByUserId,
                'used_at' => now(),
                'notes' => $notes,
            ]);
        });
    }
}
