<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Classcard Assignment</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Update assigned card, dates, expiry, or grant a custom admin extension.</p>
            </div>

            <a href="{{ route('admin.classcards.classcard-purchases') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
            <form method="POST"
                action="{{ route('admin.classcards.classcard-purchases.update', ['userClassCard' => $userClassCard->id]) }}"
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Assignment Details</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">General class card assignment settings.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Student</label>
                        <select name="user_id" required
                                class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected(old('user_id', $userClassCard->user_id) == $student->id)>
                                    {{ $student->name }} ({{ $student->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Class Card</label>
                        <select name="class_card_id" required
                                class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($cards as $card)
                                <option value="{{ $card->id }}" @selected(old('class_card_id', $userClassCard->class_card_id) == $card->id)>
                                    {{ $card->name }} ({{ $card->total_classes }} classes / {{ $card->validity_weeks }} weeks)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Purchased At</label>
                        <input type="date" name="purchased_at"
                               value="{{ old('purchased_at', optional($userClassCard->purchased_at)->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Expires At</label>
                        <input type="date" name="expires_at"
                               value="{{ old('expires_at', optional($userClassCard->expires_at)->format('Y-m-d')) }}"
                               class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Classes Remaining</label>
                        <input type="number" name="classes_remaining"
                               value="{{ old('classes_remaining', $userClassCard->classes_remaining) }}"
                               class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('admin.classcards.classcard-purchases') }}"
                       class="px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-6 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        Save Changes
                    </button>
                </div>
            </form>

            <form method="POST"
                  action="{{ route('admin.classcards.classcard-purchases.update', ['userClassCard' => $userClassCard->id]) }}"
                  class="h-fit rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm dark:border-amber-900/60 dark:bg-amber-950/20 space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="extension_only" value="1">

                <div>
                    <div class="flex items-center gap-2">
                        <i class="bx bx-calendar-plus text-xl text-amber-700 dark:text-amber-300"></i>
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Extend Expiry</h2>
                    </div>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">Admin-only manual extension. No payment or order will be created.</p>
                </div>

                <div class="rounded-xl border border-amber-200 bg-white/80 p-4 dark:border-amber-900/50 dark:bg-gray-900/60">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Current expiry</div>
                    <div class="mt-1 text-lg font-extrabold text-gray-900 dark:text-white">
                        {{ optional($userClassCard->expires_at)->format('d M Y') ?? 'No expiry set' }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $userClassCard->classes_remaining }} classes remaining · {{ ucfirst($userClassCard->status) }}
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Extension</label>
                    <select name="extension" required
                            class="mt-1 w-full rounded-xl border-amber-200 bg-white dark:border-amber-900 dark:bg-gray-900 dark:text-white">
                        <option value="1_week">+1 week</option>
                        <option value="2_weeks">+2 weeks</option>
                        <option value="1_month">+1 month</option>
                        <option value="3_months">+3 months</option>
                        <option value="custom">Custom expiry date</option>
                    </select>
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Quick extensions start from the current expiry if it is still valid, otherwise from today.</p>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Custom New Expiry Date</label>
                    <input type="date" name="new_expiry_date"
                           value="{{ old('new_expiry_date') }}"
                           class="mt-1 w-full rounded-xl border-amber-200 bg-white dark:border-amber-900 dark:bg-gray-900 dark:text-white">
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Required only when “Custom expiry date” is selected.</p>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Reason / Note <span class="font-normal text-gray-400">(optional)</span></label>
                    <textarea name="reason" rows="3" maxlength="500"
                              placeholder="e.g. Medical leave, studio closure, goodwill extension"
                              class="mt-1 w-full rounded-xl border-amber-200 bg-white dark:border-amber-900 dark:bg-gray-900 dark:text-white">{{ old('reason') }}</textarea>
                </div>

                <button type="submit"
                        onclick="return confirm('Extend this class card expiry? No payment will be created.')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-amber-700">
                    <i class="bx bx-calendar-plus"></i>
                    Extend Class Card
                </button>

                <p class="text-[11px] text-gray-500 dark:text-gray-400">The previous expiry, new expiry, admin, reason, and timestamp are recorded in the audit log.</p>
            </form>
        </div>
    </div>
</x-app-layout>
