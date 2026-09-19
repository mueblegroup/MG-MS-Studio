<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="referrer" content="no-referrer">
    <title>Class check-in · ClassM8</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 p-4 text-gray-900 sm:p-8">
    <main class="mx-auto mt-8 max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-emerald-700">ClassM8 · {{ current_studio()?->name }}</p>
        <h1 class="mt-4 text-2xl font-bold">{{ $error ? 'Unable to check in' : ($alreadyAttended ? 'You’re checked in' : 'Confirm your attendance') }}</h1>
        @if($error)
            <p class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-900" role="alert">{{ $error }}</p>
        @else
            <p class="mt-4 text-lg font-semibold">{{ $session instanceof \App\Models\ClassSession ? $session->classModel->name : $session->plan->name }}</p>
            <p class="mt-2 text-sm text-gray-600">{{ $session->start_time->format('d M Y, H:i') }} – {{ $session->end_time->format('H:i') }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ config('app.studio_timezone', config('app.timezone')) }}</p>
            @if($session->venue_name)<p class="mt-1 text-sm text-gray-600">{{ $session->venue_name }}</p>@endif
            <div class="my-5 rounded-xl bg-gray-50 p-4">
                <p class="text-xs text-gray-500">Checking in as</p>
                <p class="mt-1 font-semibold">{{ auth()->user()->name }}</p>
                <p class="mt-1 break-all text-sm text-gray-600">{{ auth()->user()->email }}</p>
            </div>
            @if($alreadyAttended)
                <p class="rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800" role="status">{{ session('success', 'Your attendance has already been recorded. You can close this page.') }}</p>
            @else
                <form method="POST" action="{{ $submitUrl }}">
                    @csrf
                    <button class="w-full rounded-xl bg-emerald-700 px-4 py-3 font-semibold text-white hover:bg-emerald-800">Confirm attendance</button>
                </form>
                <p class="mt-3 text-xs text-gray-500">Check-in closes at {{ $session->end_time->format('H:i') }}. Only confirm if you are attending this class.</p>
            @endif
        @endif
        <a href="{{ route('student.attendance.index') }}" class="mt-6 inline-block text-sm font-semibold text-emerald-700 underline">View my attendance</a>
    </main>
</body>
</html>
