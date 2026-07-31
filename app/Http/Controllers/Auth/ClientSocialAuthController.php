<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\PlatformSsoService;
use App\Support\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class ClientSocialAuthController extends Controller
{
    public function providers(PlatformSsoService $sso): JsonResponse
    {
        abort_if(app(TenantManager::class)->current(), 404);

        return response()->json([
            'providers' => collect(array_keys($sso->enabledProviders()))
                ->map(fn (string $provider) => [
                    'id' => $provider,
                    'label' => match ($provider) {
                        'google' => 'Continue with Google',
                        'microsoft' => 'Continue with Microsoft',
                        'apple' => 'Continue with Apple',
                    },
                    'url' => route('client-sso.redirect', ['provider' => $provider]),
                ])->values(),
        ]);
    }

    public function redirect(string $provider, PlatformSsoService $sso): RedirectResponse
    {
        abort_if(app(TenantManager::class)->current(), 404);
        $sso->configure($provider);

        $driver = Socialite::driver($provider);

        if ($provider === 'google') {
            $driver->scopes(['openid', 'profile', 'email']);
        } elseif ($provider === 'microsoft') {
            $driver->scopes(['openid', 'profile', 'email', 'User.Read']);
        } elseif ($provider === 'apple') {
            $driver->scopes(['name', 'email']);
        }

        return $driver->redirect();
    }

    public function callback(string $provider, PlatformSsoService $sso): RedirectResponse
    {
        abort_if(app(TenantManager::class)->current(), 404);

        try {
            $sso->configure($provider);
            $socialUser = Socialite::driver($provider)->user();
            $providerId = (string) $socialUser->getId();
            $email = strtolower(trim((string) $socialUser->getEmail()));

            if ($providerId === '' || $email === '') {
                throw new \RuntimeException('The identity provider did not return a usable email address.');
            }

            $created = false;

            $user = DB::transaction(function () use ($provider, $providerId, $email, $socialUser, &$created): User {
                $account = SocialAccount::query()
                    ->where('provider', $provider)
                    ->where('provider_user_id', $providerId)
                    ->with('user')
                    ->first();

                if ($account) {
                    abort_unless($account->user?->role === 'admin' && ! $account->user?->studio_id, 403, 'This identity is not linked to a client portal account.');
                    return $account->user;
                }

                $user = User::query()
                    ->whereNull('studio_id')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();

                if ($user) {
                    abort_unless($user->role === 'admin', 403, 'This email belongs to a non-client account.');
                } else {
                    $name = trim((string) $socialUser->getName());
                    $user = User::create([
                        'studio_id' => null,
                        'name' => $name !== '' ? $name : Str::headline(Str::before($email, '@')),
                        'email' => $email,
                        'role' => 'admin',
                        'password' => Hash::make(Str::random(64)),
                    ]);
                    $created = true;
                }

                if (! $user->email_verified_at) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }

                SocialAccount::query()->updateOrCreate(
                    ['provider' => $provider, 'provider_user_id' => $providerId],
                    [
                        'user_id' => $user->id,
                        'provider_email' => $email,
                        'avatar_url' => $socialUser->getAvatar(),
                    ]
                );

                return $user;
            });

            Auth::login($user, true);
            request()->session()->regenerate();

            return $created
                ? redirect()->route('customer.account', ['complete' => 1])
                : redirect()->route('customer.dashboard');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'email' => 'Social login failed: '.$exception->getMessage(),
            ]);
        }
    }
}
