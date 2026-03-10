<?php

namespace App\Http\Controllers;

use App\Models\ClassCard;
use App\Models\User;
use App\Models\UserClassCard;


use Illuminate\Http\Request;
use Illuminate\Support\Carbon;



class UserClassCardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $purchases = UserClassCard::query()
            ->with(['user:id,name,email', 'card:id,name,total_classes,validity_weeks,price'])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('card', function ($c) use ($search) {
                    $c->where('name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.classcards.classcard-purchases.index', compact('purchases', 'search', 'perPage'));
    }

    public function create(Request $request)
    {
        $cards = ClassCard::where('is_active', true)->orderBy('name')->get();
        $students = User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.classcards.classcard-purchases.create', compact('cards', 'students'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'user_id'        => 'required|exists:users,id',
        'class_card_id'  => 'required|exists:class_cards,id',
        'purchased_at'   => 'nullable|date',
        'classes_remaining' => 'nullable|integer|min:0', // ✅ not required
        'expires_at'     => 'nullable|date|after_or_equal:purchased_at',
    ]);

    $card = ClassCard::findOrFail($validated['class_card_id']);

    $purchasedAt = !empty($validated['purchased_at'])
        ? Carbon::parse($validated['purchased_at'])
        : now();

    $expiresAt = !empty($validated['expires_at'])
        ? Carbon::parse($validated['expires_at'])
        : $purchasedAt->copy()->addWeeks((int)$card->validity_weeks);

    // ✅ default remaining from card total_classes
    $classesRemaining = array_key_exists('classes_remaining', $validated) && $validated['classes_remaining'] !== null
        ? (int)$validated['classes_remaining']
        : (int)$card->total_classes;

    $userClassCard = UserClassCard::create([
        'user_id'           => (int)$validated['user_id'],
        'class_card_id'     => (int)$card->id,
        'purchased_at'      => $purchasedAt,
        'expires_at'        => $expiresAt,
        'classes_remaining' => $classesRemaining,
        'status'            => $expiresAt->lt(now()) ? 'expired' : 'active',
    ]);

    return redirect()
        ->route('admin.classcards.classcard-purchases')
        ->with('success', 'Class card assigned to student.');
}
    public function show(UserClassCard $userClassCard)
    {
        return view('admin.classcards.classcard-purchases.show', compact('userClassCard'));
    }

    public function edit(UserClassCard $userClassCard)
    {
        $cards = ClassCard::where('is_active', true)->orderBy('name')->get();
        $students = User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.classcards.classcard-purchases.edit', compact('userClassCard', 'cards', 'students'));
    }
    


    public function update(Request $request, UserClassCard $userClassCard)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'class_card_id' => 'required|exists:class_cards,id',
            'purchased_at' => 'nullable|date',
            'classes_remaining' => 'nullable|integer',
            'expires_at' => 'nullable|date|after_or_equal:purchased_at',
        ]);

        $card = ClassCard::findOrFail($validated['class_card_id']);

        $purchasedAt = isset($validated['purchased_at'])
            ? Carbon::parse($validated['purchased_at'])
            : now();

        $expiresAt = isset($validated['expires_at'])
            ? Carbon::parse($validated['expires_at'])
            : $purchasedAt->copy()->addWeeks($card->validity_weeks);

        $userClassCard->update([
            'user_id' => $validated['user_id'],
            'class_card_id' => $card->id,
            'purchased_at' => $purchasedAt,
            'expires_at' => $expiresAt,
            'classes_remaining' => $validated['classes_remaining'],
            'status' => $expiresAt->lt(now()) ? 'expired' : 'active',
        ]);

        return redirect()->route('admin.classcards.classcard-purchases')->with('success', 'Class card assigned to student.');
    }

    public function destroy(UserClassCard $userClassCard)
    {
        $userClassCard->delete(); // soft delete

        return redirect()->route('admin.classcards.classcard-purchases')->with('success', 'Purchase record removed.');
    }
}
