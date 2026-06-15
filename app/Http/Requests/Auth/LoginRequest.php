<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\TenantManager;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * Central platform login must only authenticate platform-level accounts.
     * Studio subdomain login must only authenticate users belonging to that studio,
     * plus the client owner account that created the studio from the customer portal.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $studio = app(TenantManager::class)->current();
        $remember = $this->boolean('remember');
        $email = Str::lower((string) $this->input('email'));
        $password = (string) $this->input('password');

        if ($studio) {
            if ($this->attemptStudioUser($studio->id, $email, $password, $remember)
                || $this->attemptStudioOwner($studio->owner_user_id, $email, $password, $remember)) {
                RateLimiter::clear($this->throttleKey());

                return;
            }
        } elseif (Auth::attempt([
            'email' => $email,
            'password' => $password,
            'studio_id' => null,
        ], $remember)) {
            RateLimiter::clear($this->throttleKey());

            return;
        }

        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    private function attemptStudioUser(int $studioId, string $email, string $password, bool $remember): bool
    {
        return Auth::attempt([
            'email' => $email,
            'password' => $password,
            'studio_id' => $studioId,
        ], $remember);
    }

    private function attemptStudioOwner(?int $ownerUserId, string $email, string $password, bool $remember): bool
    {
        if (! $ownerUserId) {
            return false;
        }

        $owner = User::query()
            ->whereKey($ownerUserId)
            ->whereNull('studio_id')
            ->where('email', $email)
            ->where('role', 'admin')
            ->first();

        if (! $owner || ! Hash::check($password, $owner->password)) {
            return false;
        }

        Auth::login($owner, $remember);

        return true;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $studioId = app(TenantManager::class)->id() ?: 'central';

        return Str::transliterate(Str::lower($this->string('email')).'|'.$studioId.'|'.$this->ip());
    }
}
