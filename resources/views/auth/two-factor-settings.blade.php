<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-white">
    <main class="mx-auto max-w-4xl px-4 py-10">
        <div class="rounded-[2rem] bg-slate-950 p-7 text-white shadow-xl">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-300">Account Security</p>
            <h1 class="mt-2 text-3xl font-black">Two-factor authentication</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Protect your account with a 6-digit code from an authenticator app. This setting is available to admins, teachers, and students.</p>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">
                {{ match(session('status')) {
                    'two-factor-enabled' => 'Two-factor authentication is now enabled.',
                    'two-factor-disabled' => 'Two-factor authentication has been disabled.',
                    'recovery-codes-regenerated' => 'New recovery codes were generated. Store them safely.',
                    default => session('status'),
                } }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700 dark:bg-red-950/30 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
                @if ($user->hasTwoFactorEnabled())
                    <div class="rounded-2xl bg-emerald-50 p-4 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200">
                        <p class="font-black">2FA is enabled</p>
                        <p class="mt-1 text-sm">Your next login will require an authenticator or recovery code.</p>
                    </div>
                    <h2 class="mt-6 text-lg font-black">Recovery codes</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Each code can be used once if you lose access to your authenticator.</p>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach (($user->two_factor_recovery_codes ?? []) as $recoveryCode)
                            <code class="rounded-xl bg-slate-100 px-3 py-2 text-center font-bold dark:bg-slate-950">{{ $recoveryCode }}</code>
                        @endforeach
                    </div>
                    <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="mt-6 space-y-3">
                        @csrf
                        <input type="password" name="password" required autocomplete="current-password" placeholder="Current password" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">
                        <button class="rounded-2xl border border-slate-300 px-5 py-3 text-sm font-black hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">Generate new recovery codes</button>
                    </form>
                @else
                    <h2 class="text-xl font-black">Set up your authenticator</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">In Google Authenticator, Microsoft Authenticator, Authy, 1Password, or Bitwarden, choose to enter a setup key manually.</p>
                    <div class="mt-5 rounded-2xl bg-slate-100 p-5 dark:bg-slate-950">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">Account</p>
                        <p class="mt-1 font-bold">{{ $user->email }}</p>
                        <p class="mt-4 text-xs font-black uppercase tracking-wider text-slate-500">Setup key</p>
                        <code class="mt-2 block break-all text-lg font-black tracking-widest text-orange-600">{{ $user->two_factor_secret }}</code>
                        <p class="mt-3 text-xs text-slate-500">Type: Time based · Digits: 6 · Period: 30 seconds</p>
                    </div>
                    <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-6 space-y-4">
                        @csrf
                        <input type="text" name="code" required inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit code" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-center text-xl font-black tracking-[0.25em] dark:border-slate-700 dark:bg-slate-950">
                        <input type="password" name="password" required autocomplete="current-password" placeholder="Current password" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">
                        <button class="w-full rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white hover:bg-orange-600">Enable two-factor authentication</button>
                    </form>
                @endif
            </section>

            <aside class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
                <h2 class="text-lg font-black">Important</h2>
                <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    <p>Save your recovery codes somewhere private and separate from this device.</p>
                    <p>Disabling 2FA requires your current password.</p>
                    <p>Studio support cannot see your authenticator secret or recovery codes.</p>
                </div>
                @if ($user->hasTwoFactorEnabled())
                    <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-6 space-y-3">
                        @csrf
                        @method('DELETE')
                        <input type="password" name="password" required autocomplete="current-password" placeholder="Current password" class="w-full rounded-2xl border border-red-200 px-4 py-3 dark:border-red-900 dark:bg-slate-950">
                        <button class="w-full rounded-2xl border border-red-300 px-5 py-3 text-sm font-black text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950/30">Disable two-factor authentication</button>
                    </form>
                @endif
                <a href="{{ url()->previous() }}" class="mt-6 inline-flex text-sm font-black text-orange-600 hover:text-orange-700">← Back to account</a>
            </aside>
        </div>

        <section class="mt-6 rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
            <h2 class="text-xl font-black">Recent account activity</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Security and account-changing actions recorded for your account.</p>
            <div class="mt-5 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($auditLogs as $log)
                    <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-bold">{{ str($log->event)->replace(['action.', '_'], ['', ' '])->headline() }}</p>
                            <p class="text-xs text-slate-500">{{ $log->ip_address ?: 'Unknown IP' }}</p>
                        </div>
                        <time class="text-xs font-bold text-slate-400">{{ $log->created_at->format('d M Y, H:i') }}</time>
                    </div>
                @empty
                    <p class="py-6 text-sm text-slate-500">No account activity has been recorded yet.</p>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
