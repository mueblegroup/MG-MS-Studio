<?php
namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CartService
{
    // App\Services\CartService.php

    public function currentCart(): Cart
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        // 1. Find or Create the base cart
        $cart = Cart::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn ($q) => $q->whereNull('user_id')->where('session_id', $sessionId))
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'is_active' => true,
            ]);
        }

        // 2. Handle Login Merging
        if ($userId) {
            $sessionCart = Cart::whereNull('user_id')
                ->where('session_id', $sessionId)
                ->where('is_active', true)
                ->first();

            if ($sessionCart && $sessionCart->id !== $cart->id) {
                $this->mergeCarts($sessionCart, $cart);
                // Refresh the relationship so the new items show up immediately
                $cart->load('items');
            }

            if (!$cart->user_id) {
                $cart->update(['user_id' => $userId]);
            }
        }

        return $cart;
    }

    public function currentCartItemCount(): int
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        $cart = Cart::query()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn ($q) => $q->whereNull('user_id')->where('session_id', $sessionId))
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (!$cart) {
            return 0;
        }

        return (int) CartItem::where('cart_id', $cart->id)->sum('quantity');
    }

    public function addItem(Model $purchasable, int $qty, float $unitPrice, string $currency = 'MYR', array $meta = []): CartItem
    {
        return DB::transaction(function () use ($purchasable, $qty, $unitPrice, $currency, $meta) {
            $cart = $this->currentCart();

            $row = CartItem::where('cart_id', $cart->id)
                ->where('purchasable_type', get_class($purchasable))
                ->where('purchasable_id', $purchasable->getKey())
                ->first();

            if ($row) {
                $row->update([
                    'quantity' => $row->quantity + $qty,
                    'unit_price' => $unitPrice, // snapshot current price
                    'currency' => $currency,
                    'meta' => $meta,
                ]);

                return $row;
            }

            return CartItem::create([
                'cart_id' => $cart->id,
                'purchasable_type' => get_class($purchasable),
                'purchasable_id' => $purchasable->getKey(),
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'currency' => $currency,
                'meta' => $meta,
            ]);
        });
    }

    public function updateQty(int $itemId, int $qty): void
    {
        $cart = $this->currentCart();

        $item = CartItem::where('cart_id', $cart->id)->where('id', $itemId)->firstOrFail();

        if ($qty <= 0) {
            $item->delete();
            return;
        }

        $item->update(['quantity' => $qty]);
    }

    public function removeItem(int $itemId): void
    {
        $cart = $this->currentCart();
        CartItem::where('cart_id', $cart->id)->where('id', $itemId)->delete();
    }

    public function clear(): void
    {
        $cart = $this->currentCart();
        CartItem::where('cart_id', $cart->id)->delete();
    }

    public function clearPurchasedItems(Order $order): void
    {
        $cart = $this->currentCart();
        $order->loadMissing('items');

        foreach ($order->items as $orderItem) {
            CartItem::where('cart_id', $cart->id)
                ->where('purchasable_type', $orderItem->purchasable_type)
                ->where('purchasable_id', $orderItem->purchasable_id)
                ->delete();
        }
    }

    protected function mergeCarts(Cart $from, Cart $to): void
    {
        DB::transaction(function () use ($from, $to) {
            foreach ($from->items as $item) {
                $existing = CartItem::where('cart_id', $to->id)
                    ->where('purchasable_type', $item->purchasable_type)
                    ->where('purchasable_id', $item->purchasable_id)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'quantity' => $existing->quantity + $item->quantity,
                    ]);
                    $item->delete();
                } else {
                    $item->update(['cart_id' => $to->id]);
                }
            }

            $from->update(['is_active' => false]);
        });
    }
}
