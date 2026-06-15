<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Studio;
use App\Models\User;
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
            'paidRevenue' => Payment::where('status', 'paid')->sum('amount'),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'upcomingClasses' => ClassSession::whereNotNull('start_time')->where('start_time', '>=', now())->count(),
            'recentStudios' => Studio::query()->with(['owner:id,name,email'])->withCount('users')->latest()->limit(8)->get(),
            'recentPayments' => Payment::query()->with(['user:id,name,email'])->latest()->limit(8)->get(),
        ]);
    }
}
