<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Studio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $event = trim($request->string('event')->toString());
        $studioId = $request->integer('studio_id');

        $logs = AuditLog::query()
            ->with(['user:id,name,email,role', 'studio:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('event', 'like', "%{$search}%")
                        ->orWhere('route', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($event !== '', fn ($query) => $query->where('event', $event))
            ->when($studioId > 0, fn ($query) => $query->where('studio_id', $studioId))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('superadmin.audit-logs.index', [
            'logs' => $logs,
            'search' => $search,
            'selectedEvent' => $event,
            'selectedStudioId' => $studioId,
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
            'studios' => Studio::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
