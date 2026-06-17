<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\StudioDomain;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $verified = $request->string('verified')->toString();
        $search = trim($request->string('search')->toString());

        $domains = StudioDomain::query()
            ->with('studio:id,name,slug,subdomain,custom_domain,status')
            ->when($type !== '', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when(in_array($verified, ['0', '1'], true), function ($query) use ($verified) {
                $query->where('is_verified', $verified === '1');
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('domain', 'like', $like)
                        ->orWhereHas('studio', function ($studioQuery) use ($like) {
                            $studioQuery->where('name', 'like', $like)
                                ->orWhere('slug', 'like', $like);
                        });
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('superadmin.domains.index', [
            'domains' => $domains,
            'type' => $type,
            'verified' => $verified,
            'search' => $search,
            'typeCounts' => StudioDomain::query()
                ->selectRaw("COALESCE(type, 'unknown') as type_name, COUNT(*) as total")
                ->groupBy('type_name')
                ->pluck('total', 'type_name'),
            'verifiedCounts' => StudioDomain::query()
                ->selectRaw('is_verified, COUNT(*) as total')
                ->groupBy('is_verified')
                ->pluck('total', 'is_verified'),
        ]);
    }
}
