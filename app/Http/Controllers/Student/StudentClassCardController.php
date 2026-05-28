<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\UserClassCard;
use Illuminate\Http\Request;

class StudentClassCardController extends Controller
{
    public function index(Request $request)
    {
        $classCards = UserClassCard::query()
            ->with(['classCard', 'usages'])
            ->where('user_id', $request->user()->id)
            ->latest('purchased_at')
            ->latest('id')
            ->get();

        $activeCards = $classCards->filter(function (UserClassCard $card) {
            return $card->status === 'active'
                && (int) $card->classes_remaining > 0
                && (!$card->expires_at || $card->expires_at->isFuture());
        });

        $expiredCards = $classCards->filter(function (UserClassCard $card) {
            return $card->status !== 'active'
                || (int) $card->classes_remaining <= 0
                || ($card->expires_at && $card->expires_at->isPast());
        });

        $summary = [
            'total_cards' => $classCards->count(),
            'active_cards' => $activeCards->count(),
            'total_remaining_classes' => $activeCards->sum('classes_remaining'),
        ];

        return view('student.classcards.index', compact('classCards', 'activeCards', 'expiredCards', 'summary'));
    }
}
