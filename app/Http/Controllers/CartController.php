<?php

namespace App\Http\Controllers;

use App\Models\ClassCard;
use App\Models\ClassSession;
use App\Models\Plan;
use App\Services\CartService;
use App\Services\StudioSettingsService;
use App\Support\TenantManager;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cartService, StudioSettingsService $settings)
    {
        $this->requireStudioContext();

        $cartModel = $cartService->currentCart()->load(['items.purchasable']);

        $currency = $settings->currency('MYR');

        $summary = [
            'currency' => $currency,
            'subtotal' => (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price),
            'total'    => (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price),
        ];

        return view('shop.cart', compact('cartModel', 'summary'));
    }

    public function add(Request $request, CartService $cart, StudioSettingsService $settings)
    {
        $studioId = $this->requireStudioContext();

        $validated = $request->validate([
            'type' => 'required|in:class_session,plan,class_card',
            'id'   => 'required|integer',
            'qty'  => 'nullable|integer|min:1|max:99',
        ]);

        $qty = (int) ($validated['qty'] ?? 1);

        $currency = $settings->currency('MYR');

        if ($validated['type'] === 'class_session') {
            $session = ClassSession::with('classModel')
                ->where('studio_id', $studioId)
                ->findOrFail($validated['id']);
            $price = (float) ($session->classModel->price ?? 0);
            $classType = $session->classModel->type ?? 'single';
            $billingInterval = $session->classModel->billing_interval ?? null;

            $meta = [
                'label' => $session->classModel->name ?? 'Class',
                'date'  => optional($session->start_time)->format('Y-m-d'),
                'time'  => optional($session->start_time)->format('H:i') . ' - ' . optional($session->end_time)->format('H:i'),
                'class_type' => $classType,
            ];

            if ($classType === 'subscription') {
                $meta['billing'] = 'Recurring ' . ucfirst($billingInterval ?: 'monthly');
            }

            $cart->addItem($session, 1, $price, $currency, $meta);
        }

        if ($validated['type'] === 'plan') {
            $plan = Plan::where('studio_id', $studioId)->findOrFail($validated['id']);
            $planCurrency = $plan->currency ?: $currency;

            $cart->addItem($plan, 1, (float) $plan->price, $planCurrency, [
                'label' => $plan->name,
            ]);
        }

        if ($validated['type'] === 'class_card') {
            $card = ClassCard::where('studio_id', $studioId)->findOrFail($validated['id']);
            $cardCurrency = $card->currency ?: $currency;

            $cart->addItem($card, $qty, (float) $card->price, $cardCurrency, [
                'label' => $card->name,
                'classes' => $card->total_classes,
                'validity_weeks' => $card->validity_weeks,
            ]);
        }

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, CartService $cart, int $itemId)
    {
        $this->requireStudioContext();

        $validated = $request->validate([
            'qty' => 'required|integer|min:0|max:99',
        ]);

        $cart->updateQty($itemId, (int) $validated['qty']);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(CartService $cart, int $itemId)
    {
        $this->requireStudioContext();

        $cart->removeItem($itemId);
        return back()->with('success', 'Removed.');
    }

    public function clear(CartService $cart)
    {
        $this->requireStudioContext();

        $cart->clear();
        return back()->with('success', 'Cart cleared.');
    }

    private function requireStudioContext(): int
    {
        $studio = app(TenantManager::class)->current();

        abort_unless($studio, 404, 'Studio shop not found.');

        return (int) $studio->id;
    }
}
