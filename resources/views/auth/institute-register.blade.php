<x-guest-layout>
    <form method="POST" action="{{ route('institutes.register.store') }}" class="space-y-6">
        @csrf

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create your studio</h1>
            <p class="mt-2 text-sm text-gray-600">
                Start your studio portal. Your students, teachers and admins will use your dedicated studio URL.
            </p>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Your studio portal format</p>
            <p class="mt-1 text-sm text-gray-700">
                Choose only the first part of your URL. The system will create:
            </p>
            <div class="mt-3 rounded-xl bg-white px-4 py-3 font-mono text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                <span id="subdomain-preview-prefix">your-studio</span>.{{ $rootDomain }}
            </div>
            <p class="mt-2 text-xs text-gray-500">
                Example: <span class="font-semibold">myacademy.{{ $rootDomain }}</span>
            </p>
        </div>

        <div>
            <x-input-label for="studio_name" value="Institute / Studio Name" />
            <x-text-input id="studio_name" class="block mt-1 w-full" type="text" name="studio_name" :value="old('studio_name')" required autofocus />
            <x-input-error :messages="$errors->get('studio_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="subdomain" value="Studio URL" />
            <div class="mt-1 flex rounded-md shadow-sm">
                <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                    https://
                </span>
                <x-text-input
                    id="subdomain"
                    class="block w-full rounded-none lowercase"
                    type="text"
                    name="subdomain"
                    :value="old('subdomain')"
                    required
                    pattern="[a-z0-9-]{3,40}"
                    maxlength="40"
                    autocomplete="off"
                    placeholder="myacademy"
                />
                <span class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 whitespace-nowrap">
                    .{{ $rootDomain }}
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-500">
                Use 3–40 characters. Only lowercase letters, numbers and hyphens. Do not enter the full domain.
            </p>
            <x-input-error :messages="$errors->get('subdomain')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="owner_name" value="Owner / Admin Name" />
            <x-text-input id="owner_name" class="block mt-1 w-full" type="text" name="owner_name" :value="old('owner_name')" required />
            <x-input-error :messages="$errors->get('owner_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="owner_email" value="Owner Email" />
            <x-text-input id="owner_email" class="block mt-1 w-full" type="email" name="owner_email" :value="old('owner_email')" required />
            <x-input-error :messages="$errors->get('owner_email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="owner_phone" value="Phone Number" />
            <x-text-input id="owner_phone" class="block mt-1 w-full" type="text" name="owner_phone" :value="old('owner_phone')" />
            <x-input-error :messages="$errors->get('owner_phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <input type="hidden" name="timezone" value="Asia/Kuala_Lumpur">
        <input type="hidden" name="currency" value="MYR">

        <div class="flex items-center justify-between gap-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                Already have an account?
            </a>

            <x-primary-button>
                Create Studio Portal
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('subdomain');
            const preview = document.getElementById('subdomain-preview-prefix');

            if (! input || ! preview) {
                return;
            }

            const updatePreview = () => {
                let value = input.value
                    .toLowerCase()
                    .replace(/[^a-z0-9-]/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '')
                    .slice(0, 40);

                input.value = value;
                preview.textContent = value || 'your-studio';
            };

            input.addEventListener('input', updatePreview);
            updatePreview();
        });
    </script>
</x-guest-layout>
