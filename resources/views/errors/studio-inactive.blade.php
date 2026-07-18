<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $studio->name }} - Temporarily Unavailable</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-white">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-2xl rounded-[2rem] bg-white p-8 text-center shadow-2xl ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 sm:p-12">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-orange-100 text-4xl text-orange-600 dark:bg-orange-950/40 dark:text-orange-300">
                <i class="bx bx-pause-circle"></i>
            </div>
            <p class="mt-6 text-sm font-black uppercase tracking-[0.22em] text-orange-500">Studio temporarily unavailable</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Oops, {{ $studio->name }} is currently inactive.</h1>
            <p class="mx-auto mt-4 max-w-xl text-sm font-semibold leading-7 text-slate-500 dark:text-slate-400">
                The studio subscription or trial has ended, so the studio portal is paused until the studio owner renews the plan from the client portal. No studio data has been deleted.
            </p>

            @if ($studio->subscription_ends_at || $studio->trial_ends_at)
                <div class="mx-auto mt-7 max-w-md rounded-2xl bg-slate-50 p-4 text-sm dark:bg-slate-950">
                    <span class="font-bold text-slate-500">Access ended:</span>
                    <strong class="ml-1">{{ optional($studio->subscription_ends_at ?? $studio->trial_ends_at)->format('d M Y H:i') }}</strong>
                </div>
            @endif

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ $billingUrl }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-6 py-3 text-sm font-black text-white hover:bg-orange-600">Studio owner: renew subscription</a>
                <a href="{{ $loginUrl }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-6 py-3 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Go to client portal</a>
            </div>

            <p class="mt-7 text-xs font-semibold leading-5 text-slate-400">Students and staff should contact the studio administrator for subscription or account assistance.</p>
        </div>
    </main>
</body>
</html>
