<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Edit Plan Session
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $plan->name }}
                </p>
            </div>

            <a href="{{ route('admin.plans.show', $plan) }}"
               class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            {{-- UPDATE FORM --}}
            <form method="POST"
                  action="{{ route('admin.plans.sessions.update', [$plan, $session]) }}"
                  class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Session Name</label>
                        <input name="session_name"
                               value="{{ old('session_name', $session->session_name) }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                               type="text">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Date</label>
                        <input name="date"
                               type="date"
                               value="{{ old('date', $session->start_time?->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Start Time</label>
                        <input name="start_time"
                               type="time"
                               value="{{ old('start_time', $session->start_time?->format('H:i')) }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">End Time</label>
                        <input name="end_time"
                               type="time"
                               value="{{ old('end_time', $session->end_time?->format('H:i')) }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Capacity (optional)</label>
                        <input name="capacity"
                               type="number"
                               min="1"
                               value="{{ old('capacity', $session->capacity) }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Venue</label>
                        <input name="venue_name"
                               value="{{ old('venue_name', $session->venue_name) }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                               type="text">
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('admin.plans.show', $plan) }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- DELETE FORM (SEPARATE, OUTSIDE UPDATE FORM) --}}
        <div class="mt-4 flex justify-start">
            <form method="POST"
                  action="{{ route('admin.plans.sessions.destroy', [$plan, $session]) }}"
                  onsubmit="return confirm('Delete this session?')">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-red-600 dark:text-red-300
                           bg-red-50 dark:bg-red-900/20 hover:bg-red-50 dark:hover:bg-red-900/20 transition mb-2">
                    Delete Session
                </button>
            </form>
        </div>

    </div>
</x-app-layout>
