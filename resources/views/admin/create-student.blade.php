<x-app-layout>
    @php
        $studioId = app(\App\Support\TenantManager::class)->id() ?: auth()->user()?->studio_id;
        $studio = $studioId ? \App\Models\Studio::query()->with('platformSubscriptionPlan')->find($studioId) : null;
        $seatUsage = $studio ? app(\App\Services\StudioSeatLimitService::class)->usage($studio) : [];
        $studentSeats = $seatUsage['student'] ?? ['used' => 0, 'limit' => null, 'unlimited' => true, 'full' => false, 'percentage' => 0];
    @endphp

    <div class="min-h-screen bg-gray-50/60 dark:bg-gray-900 p-6 sm:p-8">
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Create Student</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Create a new student user.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
                <div class="font-bold">Student could not be created</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @unless($studentSeats['unlimited'])
            <div class="mb-6 rounded-2xl border {{ $studentSeats['full'] ? 'border-red-300 bg-red-50 dark:border-red-900/60 dark:bg-red-950/30' : 'border-amber-300 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/30' }} p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-sm font-extrabold text-gray-900 dark:text-white">
                            {{ $studentSeats['full'] ? 'Student seat limit reached' : 'Student seat usage' }}
                        </div>
                        <div class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ $studentSeats['used'] }}/{{ $studentSeats['limit'] }} student seats used.
                            {{ $studentSeats['full'] ? 'Upgrade your plan before adding another student.' : 'Upgrade now to unlock more student seats.' }}
                        </div>
                    </div>
                    <a href="{{ route('admin.subscription.upgrade') }}" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-amber-700">
                        Upgrade plan
                    </a>
                </div>
            </div>
        @endunless

        @if($studentSeats['full'])
            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-sm font-semibold text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                The create form is disabled because all student seats are currently occupied.
            </div>
        @else
            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <label for="password_confirmation" class="mt-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        <i class="bx bx-plus"></i>
                        Create Student
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
