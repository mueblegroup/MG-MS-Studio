<?php

namespace App\Http\Controllers;

use App\Models\ClassCard;
use App\Models\ClassModel;
use App\Models\Plan;
use App\Support\TenantManager;
use Illuminate\Http\Request;

class GroupedShopController extends Controller
{
    public function index(Request $request)
    {
        $studio = app(TenantManager::class)->current();
        abort_unless($studio, 404, 'Studio shop not found.');

        $studioId = (int) $studio->id;
        $tab = $request->query('tab', 'classes');
        $q = trim((string) $request->query('q', ''));
        $now = now();

        $classMinStart = $now->copy()->startOfDay()->addDays((int) config('shop.class_early_cutoff_days', 0));
        $planMinUntilDate = $now->copy()->startOfDay()->addDays((int) config('shop.plan_early_cutoff_days', 0));

        $classes = ClassModel::query()
            ->where('studio_id', $studioId)
            ->with([
                'teacher:id,name,email',
                'sessions' => fn ($query) => $query
                    ->whereNotNull('start_time')
                    ->where('start_time', '>=', $classMinStart)
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
                    })
                    ->orderBy('start_time'),
            ])
            ->whereHas('sessions', fn ($query) => $query
                ->whereNotNull('start_time')
                ->where('start_time', '>=', $classMinStart)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
                }))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($wrap) use ($q) {
                    $wrap->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('teacher', fn ($teacher) => $teacher
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%"));
                });
            })
            ->orderBy('name')
            ->paginate(12, ['*'], 'classes_page')
            ->withQueryString();

        $plans = Plan::query()
            ->where('studio_id', $studioId)
            ->with(['sessions' => fn ($query) => $query
                ->whereNotNull('start_time')
                ->where('start_time', '>=', $now)
                ->orderBy('start_time')])
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->where(function ($query) use ($planMinUntilDate) {
                $query->whereNull('until_date')
                    ->orWhereDate('until_date', '>=', $planMinUntilDate->toDateString());
            })
            ->whereHas('sessions', fn ($query) => $query
                ->whereNotNull('start_time')
                ->where('start_time', '>=', $now))
            ->orderBy('name')
            ->paginate(12, ['*'], 'plans_page')
            ->withQueryString();

        $classcards = ClassCard::query()
            ->where('studio_id', $studioId)
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('class_cards', 'is_active'), fn ($query) => $query->where('is_active', 1))
            ->orderBy('name')
            ->paginate(12, ['*'], 'cards_page')
            ->withQueryString();

        return view('shop.grouped-index', compact('tab', 'q', 'classes', 'plans', 'classcards'));
    }
}
