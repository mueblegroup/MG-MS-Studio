<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use App\Support\ApiAbilities;
use App\Support\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    public function index(Request $request): View
    {
        $tokens = PersonalAccessToken::query()
            ->where('tokenable_type', get_class($request->user()))
            ->where('tokenable_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        $logs = ApiRequestLog::query()
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.api-tokens.index', [
            'tokens' => $tokens,
            'logs' => $logs,
            'abilityLabels' => ApiAbilities::labels(),
            'plainTextToken' => session('plain_text_token'),
        ]);
    }

    public function create(): View
    {
        return view('admin.api-tokens.create', [
            'abilityGroups' => ApiAbilities::grouped(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $studio = app(TenantManager::class)->current();
        abort_unless($studio, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $allowedAbilities = ApiAbilities::all();
        $abilities = collect($validated['abilities'])
            ->filter(fn ($ability) => in_array($ability, $allowedAbilities, true))
            ->values()
            ->all();

        if (in_array('*', $validated['abilities'], true)) {
            $abilities = ['*'];
        }

        if (empty($abilities)) {
            return back()
                ->withErrors(['abilities' => 'Please select at least one valid API permission.'])
                ->withInput();
        }

        // Keep the tenant binding as an internal Sanctum ability so the API
        // can resolve the exact studio before route model binding/controllers.
        $abilities[] = 'studio:'.$studio->id;
        $abilities = array_values(array_unique($abilities));

        $expiresAt = !empty($validated['expires_at'])
            ? Carbon::parse($validated['expires_at'])
            : null;

        $newToken = $request->user()->createToken(
            $validated['name'],
            $abilities,
            $expiresAt,
        );

        return redirect()
            ->route('admin.api-tokens.index')
            ->with('plain_text_token', $newToken->plainTextToken)
            ->with('status', 'API token created successfully for this studio. Copy it now because it will not be shown again.');
    }

    public function destroy(Request $request, PersonalAccessToken $apiToken): RedirectResponse
    {
        abort_unless(
            $apiToken->tokenable_type === get_class($request->user()) && (int) $apiToken->tokenable_id === (int) $request->user()->id,
            403
        );

        $apiToken->delete();

        return redirect()
            ->route('admin.api-tokens.index')
            ->with('status', 'API token revoked successfully.');
    }

    public function docs(): View
    {
        return view('admin.api-tokens.docs', [
            'abilityGroups' => ApiAbilities::grouped(),
            'baseUrl' => url('/api/v1'),
        ]);
    }
}
