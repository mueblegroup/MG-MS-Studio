<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Assign Plan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Assign a plan to a student.</p>
            </div>

            <a href="{{ route('admin.planassignments.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

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
            <form method="POST" action="{{ route('admin.planassignments.store') }}" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Student</label>
                        <select name="user_id" required
                                class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected(old('user_id') == $s->id)>
                                    {{ $s->name }} ({{ $s->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Plan</label>
                        <select name="plan_id" required
                                class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}" @selected(old('plan_id') == $p->id)>
                                    {{ $p->name }} — {{ $p->currency ?? 'MYR' }} {{ number_format($p->price ?? 0, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Start Date (optional)</label>
                        <input type="date" name="starts_on" value="{{ old('starts_on') }}"
                               class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">End Date (optional)</label>
                        <input type="date" name="ends_on" value="{{ old('ends_on') }}"
                               class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Active</label>
                        <select name="is_active" required
                                class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="1" @selected(old('is_active', '1') == '1')>Yes</option>
                            <option value="0" @selected(old('is_active') == '0')>No</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('admin.planassignments.index') }}"
                       class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-6 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
