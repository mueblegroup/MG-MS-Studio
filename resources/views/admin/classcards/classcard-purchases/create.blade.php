<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Assign Class Card</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manually assign a class card to a student.</p>
            </div>

            <a href="{{ route('admin.classcards.classcard-purchases') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                      hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        @if($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="POST" action="{{ route('admin.classcards.classcard-purchases.store') }}" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
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

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Class Card</label>
                        <select name="class_card_id" required
                                class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($cards as $c)
                                <option value="{{ $c->id }}" @selected(old('class_card_id') == $c->id)>
                                    {{ $c->name }} — {{ $c->total_classes }} credits / {{ $c->validity_weeks }} weeks — RM {{ number_format($c->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Purchased At (optional)</label>
                        <input type="date" name="purchased_at" value="{{ old('purchased_at') }}"
                               class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Expires At (optional)</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                               class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('admin.classcards.classcard-purchases') }}"
                       class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                        Cancel
                    </a>
                    <button class="px-6 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
