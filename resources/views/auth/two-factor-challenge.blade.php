<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-white">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-[2rem] bg-white p-7 shadow-xl ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-500 text-2xl text-white">🔐</div>
            <h1 class="mt-5 text-2xl font-black">Confirm it’s you</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Enter the 6-digit code from your authenticator app. You can also use one unused recovery code.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-2xl bg-red-50 p-4 text-sm font-bold text-red-700 dark:bg-red-950/30 dark:text-red-200">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('two-factor.challenge.verify') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="code" class="mb-2 block text-sm font-bold">Authentication code</label>
                    <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" autofocus class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-center text-2xl font-black tracking-[0.35em] dark:border-slate-700 dark:bg-slate-950" placeholder="000000">
                </div>
                <div class="text-center text-xs font-bold uppercase tracking-widest text-slate-400">or</div>
                <div>
                    <label for="recovery_code" class="mb-2 block text-sm font-bold">Recovery code</label>
                    <input id="recovery_code" name="recovery_code" autocomplete="one-time-code" class="w-full rounded-2xl border border-slate-300 px-4 py-3 font-mono uppercase dark:border-slate-700 dark:bg-slate-950" placeholder="ABCDE-FGHIJ">
                </div>
                <button class="w-full rounded-2xl bg-orange-500 px-5 py-3.5 text-sm font-black text-white hover:bg-orange-600">Verify and continue</button>
            </form>

            <a href="{{ route('login') }}" class="mt-5 block text-center text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white">Back to login</a>
        </div>
    </main>
</body>
</html>
