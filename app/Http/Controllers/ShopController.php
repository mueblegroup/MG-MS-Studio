<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Plan;
use App\Models\ClassCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;


class ShopController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'classes'); // classes | plans | classcards
        $q = trim((string) $request->query('q', ''));

        $now = now();
        $cutoffDays = (int) config('shop.plan_early_cutoff_days', 0);

        // If cutoffDays=3, a plan expiring on Dec 3 will stop showing from Nov 30.
        // Equivalent check: until_date >= today + cutoffDays
        $planMinUntilDate = $now->copy()->startOfDay()->addDays($cutoffDays);

        $classCutoffDays = (int) config('shop.class_early_cutoff_days', 0);
        $classMinStart = $now->copy()->startOfDay()->addDays($classCutoffDays);

        $classes = ClassSession::query()
            ->with(['classModel.teacher:id,name,email'])
            ->whereNotNull('start_time')
            // ✅ hide past + apply early cutoff
            ->where('start_time', '>=', $classMinStart)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($wrap) use ($q) {
                    $wrap->whereHas('classModel', function ($c) use ($q) {
                            $c->where('name', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                        })
                        ->orWhereHas('classModel.teacher', function ($t) use ($q) {
                            $t->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('start_time')
            ->paginate(12)
            ->withQueryString();

        $plans = Plan::query()
            ->with(['sessions' => function ($s) use ($now) {
                $s->whereNotNull('start_time')
                ->where('start_time', '>=', $now)
                ->orderBy('start_time');
            }])
            ->when($q !== '', fn($query) => $query->where('name', 'like', "%{$q}%"))
            // ✅ apply early cutoff only if until_date exists
            ->where(function ($query) use ($planMinUntilDate) {
                $query->whereNull('until_date')
                    ->orWhereDate('until_date', '>=', $planMinUntilDate->toDateString());
            })
            // ✅ must have future sessions (prevents “valid until” but no upcoming sessions)
            ->whereHas('sessions', function ($s) use ($now) {
                $s->whereNotNull('start_time')
                ->where('start_time', '>=', $now);
            })
            ->orderBy('name')
            ->paginate(12, ['*'], 'plans_page')
            ->withQueryString();

        $classcards = ClassCard::query()
            ->when($q !== '', fn($query) => $query->where('name', 'like', "%{$q}%"))
            // If you have is_active column, keep this:
            ->when(\Schema::hasColumn('class_cards', 'is_active'), fn($qq) => $qq->where('is_active', 1))
            ->orderBy('name')
            ->paginate(12, ['*'], 'cards_page')
            ->withQueryString();

        return view('shop.index', compact('tab', 'q', 'classes', 'plans', 'classcards'));
    }

    public function cart()
    {
        $cart = session('cart', []);
        $summary = $this->cartSummary($cart);

        return view('shop.cart', [
            'cart' => $cart,
            'summary' => $summary,
        ]);
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:class_session,plan,class_card',
            'id' => 'required|integer|min:1',
            'qty' => 'nullable|integer|min:1|max:20',
        ]);

        $qty = (int)($validated['qty'] ?? 1);

        // Load product (and price/name) safely
        $product = $this->resolveProduct($validated['type'], (int)$validated['id']);

        if (!$product) {
            return back()->with('error', 'Item not found.');
        }

        $cart = session('cart', []);

        // Unique cart key so the same item stacks
        $key = $validated['type'] . ':' . $validated['id'];

        if (!isset($cart[$key])) {
            $cart[$key] = [
                'key' => $key,
                'type' => $validated['type'],
                'id' => (int)$validated['id'],
                'name' => $product['name'],
                'price' => (float)$product['price'],
                'currency' => $product['currency'] ?? 'MYR',
                'meta' => $product['meta'] ?? [],
                'qty' => 0,
            ];
        }

        $cart[$key]['qty'] = min(20, (int)$cart[$key]['qty'] + $qty);

        session(['cart' => $cart]);

        return redirect()->route('shop.cart')->with('success', 'Added to cart.');
    }

    public function updateCart(Request $request, string $key)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:1|max:20',
        ]);

        $cart = session('cart', []);
        if (!isset($cart[$key])) {
            return back()->with('error', 'Cart item not found.');
        }

        $cart[$key]['qty'] = (int)$validated['qty'];
        session(['cart' => $cart]);

        return back()->with('success', 'Cart updated.');
    }

    public function removeFromCart(string $key)
    {
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);

        return back()->with('success', 'Item removed.');
    }

    public function clearCart()
    {
        session()->forget('cart');
        return back()->with('success', 'Cart cleared.');
    }

    public function checkout()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        $summary = $this->cartSummary($cart);

        return view('shop.checkout', [
            'cart' => $cart,
            'summary' => $summary,
        ]);
    }

    private function cartSummary(array $cart): array
    {
        $subtotal = 0;
        $currency = 'MYR';

        foreach ($cart as $item) {
            $currency = $item['currency'] ?? $currency;
            $subtotal += ((float)$item['price']) * ((int)$item['qty']);
        }

        return [
            'currency' => $currency,
            'subtotal' => $subtotal,
            'total' => $subtotal, // later add fees/discounts/tax
        ];
    }

    private function resolveProduct(string $type, int $id): ?array
    {
        if ($type === 'class_session') {
            $session = ClassSession::with(['classModel.teacher:id,name,email'])->find($id);
            if (!$session || !$session->classModel) return null;

            $start = optional($session->start_time)->format('Y-m-d H:i');
            $teacher = $session->classModel->teacher?->name ?? '-';

            return [
                'name' => ($session->classModel->name ?? 'Class') . " ({$start})",
                'price' => (float)($session->classModel->price ?? 0),
                'currency' => 'MYR',
                'meta' => [
                    'date' => optional($session->start_time)->format('Y-m-d'),
                    'time' => optional($session->start_time)->format('H:i') . ' - ' . optional($session->end_time)->format('H:i'),
                    'teacher' => $teacher,
                    'venue' => $session->venue_name,
                ],
            ];
        }

        if ($type === 'plan') {
            $plan = Plan::find($id);
            if (!$plan) return null;

            return [
                'name' => $plan->name,
                'price' => (float)($plan->price ?? 0),
                'currency' => $plan->currency ?? 'MYR',
                'meta' => [
                    'sessions_count' => $plan->sessions->count(),
                ],
                'session_dates' => $plan->sessions->pluck('date')->toArray(),
            ];
        }

        if ($type === 'class_card') {
            $card = ClassCard::find($id);
            if (!$card) return null;

            return [
                'name' => $card->name,
                'price' => (float)($card->price ?? 0),
                'currency' => 'MYR',
                'meta' => [
                    'total_classes' => $card->total_classes,
                    'validity_weeks' => $card->validity_weeks,
                ],
            ];
        }

        return null;
    }
}
