<section id="two-factor" class="space-y-5">
    <div>
        <h2 class="text-xl font-black text-slate-950 dark:text-white">Two-Factor Authentication</h2>
        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">Add a 6-digit authenticator code after your password. You can disable it later using your current password.</p>
    </div>

    @if (session('status') && str_contains((string) session('status'), 'two-factor'))
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">
            {{ session('status') === 'two-factor-enabled' ? 'Two-factor authentication is now enabled.' : 'Two-factor authentication has been disabled.' }}
        </div>
    @elseif (session('status') === 'recovery-codes-regenerated')
        <div class="rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">New recovery codes were generated. Store them safely.</div>
    @endif

    @if ($errors->has('code'))
        <div class="rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700 dark:bg-red-950/30 dark:text-red-200">{{ $errors->first('code') }}</div>
    @endif

    @if ($user->hasTwoFactorEnabled())
        <div class="rounded-2xl bg-emerald-50 p-4 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200">
            <p class="font-black">2FA is enabled</p>
            <p class="mt-1 text-sm">Your next login will require an authenticator code or one recovery code.</p>
        </div>

        <div>
            <h3 class="font-black text-slate-950 dark:text-white">Recovery codes</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Each code works once. Keep them somewhere private.</p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach (($user->two_factor_recovery_codes ?? []) as $recoveryCode)
                    <code class="rounded-xl bg-slate-100 px-3 py-2 text-center font-bold dark:bg-slate-950">{{ $recoveryCode }}</code>
                @endforeach
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="space-y-3 rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                @csrf
                <p class="font-black text-slate-950 dark:text-white">Replace recovery codes</p>
                <input type="password" name="password" required autocomplete="current-password" placeholder="Current password" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">
                <button class="w-full rounded-2xl border border-slate-300 px-5 py-3 text-sm font-black hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">Generate new codes</button>
            </form>

            <form method="POST" action="{{ route('two-factor.disable') }}" class="space-y-3 rounded-2xl border border-red-200 p-4 dark:border-red-900/60">
                @csrf
                @method('DELETE')
                <p class="font-black text-red-700 dark:text-red-300">Disable 2FA</p>
                <input type="password" name="password" required autocomplete="current-password" placeholder="Current password" class="w-full rounded-2xl border border-red-200 px-4 py-3 dark:border-red-900 dark:bg-slate-950">
                <button class="w-full rounded-2xl border border-red-300 px-5 py-3 text-sm font-black text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950/30">Disable two-factor authentication</button>
            </form>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
            <div class="rounded-2xl bg-white p-4 text-center ring-1 ring-slate-200 dark:bg-white" data-two-factor-qr data-uri="{{ $twoFactorProvisioningUri }}">
                <div class="mx-auto flex min-h-[240px] items-center justify-center" data-qr-target></div>
                <p class="mt-2 text-xs font-bold text-slate-500">Scan with your authenticator app</p>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl bg-slate-100 p-5 dark:bg-slate-950">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-500">Manual setup key</p>
                    <code class="mt-2 block break-all text-lg font-black tracking-widest text-orange-600">{{ $user->two_factor_secret }}</code>
                    <p class="mt-3 text-xs text-slate-500">Time based · 6 digits · 30-second period</p>
                </div>

                <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-4">
                    @csrf
                    <input type="text" name="code" required inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit authenticator code" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-center text-xl font-black tracking-[0.25em] dark:border-slate-700 dark:bg-slate-950">
                    <input type="password" name="password" required autocomplete="current-password" placeholder="Current password" class="w-full rounded-2xl border border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-950">
                    <button class="w-full rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white hover:bg-orange-600">Enable two-factor authentication</button>
                </form>
            </div>
        </div>
    @endif
</section>

@once
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-two-factor-qr]').forEach((container) => {
                const uri = container.dataset.uri;
                const target = container.querySelector('[data-qr-target]');
                if (!uri || !target || typeof qrcode === 'undefined') return;
                const qr = qrcode(0, 'M');
                qr.addData(uri);
                qr.make();
                target.innerHTML = qr.createImgTag(6, 8);
                const image = target.querySelector('img');
                if (image) image.className = 'h-auto max-w-full';
            });
        });
    </script>
@endonce