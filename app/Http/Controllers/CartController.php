<?php

namespace App\Http\Controllers;

use App\Models\ClassCard;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ClassSession;
use App\Models\Plan;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cartService) // Renamed variable for clarity
    {
        // Fetch the cart and eager load the items and their related polymorphic models
        $cartModel = $cartService->currentCart()->load(['items.purchasable']);

        $summary = [
            'currency' => 'MYR',
            'subtotal' => (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price),
            'total' => (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price),
        ];

        return view('shop.cart', compact('cartModel', 'summary'));
    }

    public function add(Request $request, CartService $cart)
    {
        $validated = $request->validate([
            'type' => 'required|in:class_session,plan,class_card',
            'id' => 'required|integer',
            'qty' => 'nullable|integer|min:1|max:99',
        ]);

        $qty = (int) ($validated['qty'] ?? 1);

        if ($validated['type'] === 'class_session') {
            $session = ClassSession::with('classModel')->findOrFail($validated['id']);
            $price = (float) ($session->classModel->price ?? 0);

            $cart->addItem($session, 1, $price, 'MYR', [
                'label' => $session->classModel->name ?? 'Class',
                'date' => optional($session->start_time)->format('Y-m-d'),
                'time' => optional($session->start_time)->format('H:i') . ' - ' . optional($session->end_time)->format('H:i'),
            ]);
        }

        if ($validated['type'] === 'plan') {
            $plan = Plan::findOrFail($validated['id']);
            $cart->addItem($plan, 1, (float) $plan->price, $plan->currency ?? 'MYR', [
                'label' => $plan->name,
            ]);
        }

        if ($validated['type'] === 'class_card') {
            $card = ClassCard::findOrFail($validated['id']);
            $cart->addItem($card, $qty, (float) $card->price, $card->currency ?? 'MYR', [
                'label' => $card->name,
                'classes' => $card->total_classes,
                'validity_weeks' => $card->validity_weeks,
            ]);
        }

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, CartService $cart, int $itemId)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:0|max:99',
        ]);

        $cart->updateQty($itemId, (int) $validated['qty']);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(CartService $cart, int $itemId)
    {
        $cart->removeItem($itemId);
        return back()->with('success', 'Removed.');
    }

    public function clear(CartService $cart)
    {
        $cart->clear();
        return back()->with('success', 'Cart cleared.');
    }
}
