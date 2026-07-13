<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSubscriptionPayment;
use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperadminController extends Controller
{
    public function dashboard(): View
    {
        $studioStatusCounts = Studio::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $paidPlatformRevenue = PlatformSubscriptionPayment::query()
            ->where('status', 'paid')
            ->sum('amount');

        $monthlyPlatformRevenue = PlatformSubscriptionPayment::query()
            ->where('status', 'paid')
            ->where(function ($query) {
                $query->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->orWhere(function ($fallback) {
                        $fallback->whereNull('paid_at')
                            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    });
            })
            ->sum('amount');

        return view('superadmin.dashboard', [
            'totalStudios' => Studio::count(),
            'activeStudios' => (int) ($studioStatusCounts['active'] ?? 0),
            'trialStudios' => (int) ($studioStatusCounts['trial'] ?? 0),
            'inactiveStudios' => (int) ($studioStatusCounts['inactive'] ?? 0),
            'totalUsers' => User::count(),
            'totalAdmins' => (int) ($roleCounts['admin'] ?? 0),
            'totalTeachers' => (int) ($roleCounts['teacher'] ?? 0),
            'totalStudents' => (int) ($roleCounts['student'] ?? 0),
            'totalSuperadmins' => (int) ($roleCounts['superadmin'] ?? 0),
            'paidPlatformRevenue' => $paidPlatformRevenue,
            'monthlyPlatformRevenue' => $monthlyPlatformRevenue,
            'activePlatformPlans' => PlatformSubscriptionPlan::where('is_active', true)->count(),
            'subscribedStudios' => Studio::whereNotNull('platform_subscription_plan_id')->count(),
            'recentStudios' => Studio::query()
                ->with(['owner:id,name,email', 'platformSubscriptionPlan:id,name,price,currency,billing_interval'])
                ->withCount('users')
                ->latest()
                ->limit(8)
                ->get(),
            'recentPlatformPayments' => PlatformSubscriptionPayment::query()
                ->with(['studio:id,name,slug', 'plan:id,name'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function studios(): View
    {
        return view('superadmin.studios.index', [
            'studios' => Studio::query()
                ->with(['owner:id,name,email', 'platformSubscriptionPlan:id,name,price,currency,billing_interval'])
                ->withCount('users')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function editStudio(Studio $studio): View
    {
        return view('superadmin.studios.edit', [
            'studio' => $studio->load(['owner:id,name,email', 'platformSubscriptionPlan']),
            'plans' => PlatformSubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get(),
        ]);
    }

    public function updateStudio(Request $request, Studio $studio): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'trial', 'inactive', 'suspended'])],
            'platform_subscription_plan_id' => ['nullable', 'exists:platform_subscription_plans,id'],
            'trial_ends_at' => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date'],
        ]);

        $plan = ! empty($validated['platform_subscription_plan_id'])
            ? PlatformSubscriptionPlan::find($validated['platform_subscription_plan_id'])
            : null;

        $studio->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'platform_subscription_plan_id' => $plan?->id,
            'plan_name' => $plan?->name,
            'trial_ends_at' => $validated['trial_ends_at'] ?? null,
            'subscription_ends_at' => $validated['subscription_ends_at'] ?? null,
        ]);

        return redirect()
            ->route('superadmin.studios.index')
            ->with('success', 'Studio subscription updated successfully.');
    }

    public function users(Request $request): View
    {
        $role = $request->string('role')->toString();
        $search = trim($request->string('search')->toString());

        $users = User::query()
            ->with('studio:id,name,slug')
            ->when(in_array($role, ['superadmin', 'admin', 'teacher', 'student'], true), function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('superadmin.users.index', [
            'users' => $users,
            'role' => $role,
            'search' => $search,
            'roleCounts' => User::query()
                ->selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),
        ]);
    }

    public function plans(): View
    {
        return view('superadmin.subscription-plans.index', [
            'plans' => PlatformSubscriptionPlan::query()
                ->withCount(['studios', 'payments'])
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get(),
        ]);
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $this->validatePlan($request);
        $validated['slug'] = $this->uniquePlanSlug($validated['name']);

        PlatformSubscriptionPlan::create($validated);

        return redirect()
            ->route('superadmin.subscription-plans.index')
            ->with('success', 'Platform subscription plan created successfully.');
    }

    public function updatePlan(Request $request, PlatformSubscriptionPlan $plan): RedirectResponse
    {
        if ($request->boolean('delete_plan')) {
            return $this->destroyPlan($plan);
        }

        $validated = $this->validatePlan($request);
        $plan->update($validated);

        return redirect()
            ->route('superadmin.subscription-plans.index')
            ->with('success', 'Platform subscription plan updated successfully.');
    }

    private function destroyPlan(PlatformSubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->studios()->exists()) {
            return redirect()
                ->route('superadmin.subscription-plans.index')
                ->with('error', 'This plan cannot be deleted because one or more studios are currently assigned to it. Reassign those studios or deactivate the plan instead.');
        }

        if ($plan->payments()->exists()) {
            return redirect()
                ->route('superadmin.subscription-plans.index')
                ->with('error', 'This plan cannot be deleted because it has payment history. Deactivate it to preserve financial records.');
        }

        $plan->delete();

        return redirect()
            ->route('superadmin.subscription-plans.index')
            ->with('success', 'Platform subscription plan deleted successfully.');
    }

    public function platformPayments(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        $payments = PlatformSubscriptionPayment::query()
            ->with(['studio:id,name,slug', 'plan:id,name'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%")
                        ->orWhereHas('studio', fn ($studioQuery) => $studioQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('superadmin.platform-payments.index', [
            'payments' => $payments,
            'status' => $status,
            'search' => $search,
            'statusCounts' => PlatformSubscriptionPayment::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'paidRevenue' => PlatformSubscriptionPayment::where('status', 'paid')->sum('amount'),
        ]);
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_interval' => ['required', Rule::in(['monthly', 'annual', 'lifetime'])],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_teachers' => ['nullable', 'integer', 'min:0'],
            'max_admins' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false, 'sort_order' => 0];
    }

    private function uniquePlanSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'plan';
        $slug = $baseSlug;
        $counter = 2;

        while (PlatformSubscriptionPlan::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
