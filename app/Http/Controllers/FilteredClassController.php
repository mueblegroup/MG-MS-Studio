<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;

class FilteredClassController extends ClassController
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $classType = (string) $request->query('class_type', 'all');
        $allowedTypes = ['all', 'single', 'recurring', 'subscription'];

        if (! in_array($classType, $allowedTypes, true)) {
            $classType = 'all';
        }

        $perPage = max(10, min(100, (int) $request->query('per_page', 10)));

        $classes = ClassModel::query()
            ->with(['teacher:id,name,email'])
            ->withCount([
                'sessions',
                'studioSubscriptions as active_subscriptions_count' => fn ($query) => $query->whereIn('status', ['active', 'trialing', 'past_due']),
            ])
            ->withMin(['sessions as first_session_at' => fn ($query) => $query->where('status', '!=', 'cancelled')], 'start_time')
            ->withMax(['sessions as last_session_at' => fn ($query) => $query->where('status', '!=', 'cancelled')], 'start_time')
            ->when($classType !== 'all', fn ($query) => $query->where('type', $classType))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('teacher', fn ($teacher) => $teacher
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.classes.index', compact('classes', 'search', 'classType', 'perPage'));
    }
}
