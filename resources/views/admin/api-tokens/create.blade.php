<x-app-layout>
    <div class="min-h-screen bg-gray-50/50 p-4 dark:bg-gray-900 sm:p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Create API Token</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Generate a secure token with only the permissions needed by the integration.</p>
        </div>

        <form method="POST" action="{{ route('admin.api-tokens.store') }}" class="max-w-5xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @csrf

            <div class="mb-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Token Name</label>
                    <input name="name" value="{{ old('name') }}" required placeholder="Example: n8n Automation" class="w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    @error('name') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Expires At</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="w-full rounded-xl border-gray-200 bg-white px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for no expiry.</p>
                    @error('expires_at') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-gray-800 dark:text-white">Permissions</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Use Full Access only for trusted internal systems.</p>
                    </div>
                </div>

                @error('abilities') <p class="mb-3 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror

                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($abilityGroups as $group => $abilities)
                        <div class="rounded-2xl border border-gray-100 p-4 dark:border-gray-700">
                            <h3 class="mb-3 text-sm font-extrabold text-gray-800 dark:text-white">{{ $group }}</h3>
                            <div class="space-y-2">
                                @foreach($abilities as $ability => $label)
                                    <label class="flex items-start gap-3 rounded-xl p-2 transition hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <input type="checkbox" name="abilities[]" value="{{ $ability }}" @checked(in_array($ability, old('abilities', []), true)) class="mt-1 rounded border-gray-300 text-[#d97706] focus:ring-[#d97706]">
                                        <span>
                                            <span class="block text-sm font-bold text-gray-700 dark:text-gray-200">{{ $ability }}</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-5 dark:border-gray-700">
                <a href="{{ route('admin.api-tokens.index') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">Cancel</a>
                <button class="rounded-xl bg-[#d97706] px-4 py-2 text-sm font-bold text-white hover:bg-[#b45309]">Create Token</button>
            </div>
        </form>
    </div>
</x-app-layout>
