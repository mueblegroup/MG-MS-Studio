<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Profile Information') }}</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Keep your contact and operational information accurate.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <x-input-label for="name" :value="__('Full name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="phone_number" :value="__('Phone number')" />
                <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" required autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    {{ __('Your email address is unverified.') }}
                    <button form="send-verification" class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-400 dark:hover:text-gray-100">{{ __('Re-send verification email') }}</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">{{ __('A new verification link has been sent.') }}</p>
                @endif
            @endif
        </div>

        @if ($user->role === 'admin' && $user->ownedStudios()->exists())
            <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
                <h3 class="font-bold text-gray-900 dark:text-white">Organisation information</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="organisation_name" :value="__('Organisation name')" />
                        <x-text-input id="organisation_name" name="organisation_name" type="text" class="mt-1 block w-full" :value="old('organisation_name', $user->organisation_name)" required autocomplete="organization" />
                        <x-input-error class="mt-2" :messages="$errors->get('organisation_name')" />
                    </div>
                    <div>
                        <x-input-label for="job_title" :value="__('Job title')" />
                        <x-text-input id="job_title" name="job_title" type="text" class="mt-1 block w-full" :value="old('job_title', $user->job_title)" autocomplete="organization-title" />
                        <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="country" :value="__('Country')" />
                        <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', $user->country)" required autocomplete="country-name" />
                        <x-input-error class="mt-2" :messages="$errors->get('country')" />
                    </div>
                </div>
            </div>
        @endif

        @if ($user->role === 'student')
            <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
                <h3 class="font-bold text-gray-900 dark:text-white">Student information</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="date_of_birth" :value="__('Date of birth')" />
                        <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d'))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                    </div>
                    <div>
                        <x-input-label for="gender" :value="__('Gender')" />
                        <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">Select</option>
                            @foreach(['female' => 'Female', 'male' => 'Male', 'non_binary' => 'Non-binary', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('gender', $user->gender) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="address" :value="__('Residential address')" />
                        <textarea id="address" name="address" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('address', $user->address) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('address')" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20">
                <h3 class="font-bold text-amber-950 dark:text-amber-100">Emergency contact</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="emergency_contact_name" :value="__('Contact name')" />
                        <x-text-input id="emergency_contact_name" name="emergency_contact_name" type="text" class="mt-1 block w-full" :value="old('emergency_contact_name', $user->emergency_contact_name)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_name')" />
                    </div>
                    <div>
                        <x-input-label for="emergency_contact_phone" :value="__('Contact phone')" />
                        <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" type="tel" class="mt-1 block w-full" :value="old('emergency_contact_phone', $user->emergency_contact_phone)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('emergency_contact_phone')" />
                    </div>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
