@php($isClientOwner = $user->role === 'admin' && ! $user->studio_id)
<section>
    <header>
        <h2 class="text-lg font-black text-gray-900 dark:text-gray-100">{{ $isClientOwner ? 'Client Admin Personal Details' : __('Profile Information') }}</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $isClientOwner ? 'Complete every required field to unlock the client portal. Fields marked with a red asterisk are mandatory.' : 'Keep your contact and operational information accurate.' }}
        </p>
    </header>

    @if($isClientOwner && ! $user->hasCompleteClientProfile())
        <div class="mt-5 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm font-bold text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-100">
            Your profile is incomplete. Dashboard, billing, studio setup, invoices and messages remain locked until this form is completed.
        </div>
    @endif

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
            <h3 class="font-black text-gray-900 dark:text-white">Personal information</h3>
            <div class="mt-4 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="name" class="text-sm font-semibold">Full name <span class="text-red-600">*</span></label>
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <label for="email" class="text-sm font-semibold">Email address <span class="text-red-600">*</span></label>
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
                <div>
                    <label for="phone_number" class="text-sm font-semibold">Mobile phone number <span class="text-red-600">*</span></label>
                    <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" required autocomplete="tel" placeholder="+60123456789" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                    @if($isClientOwner)
                        <p class="mt-1 text-xs {{ $user->phone_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $user->phone_verified_at ? 'Phone number verified' : 'Phone verification will be added in the next phase.' }}</p>
                    @endif
                </div>
                @if($isClientOwner || $user->role === 'student')
                    <div>
                        <label for="date_of_birth" class="text-sm font-semibold">Date of birth <span class="text-red-600">*</span></label>
                        <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d'))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                    </div>
                    <div>
                        <label for="gender" class="text-sm font-semibold">Gender <span class="text-red-600">{{ $isClientOwner ? '*' : '' }}</span></label>
                        <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900" @required($isClientOwner)>
                            <option value="">Select</option>
                            @foreach(['female'=>'Female','male'=>'Male','non_binary'=>'Non-binary','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $value=>$label)
                                <option value="{{ $value }}" @selected(old('gender', $user->gender) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                    </div>
                @endif
            </div>
        </div>

        @if($isClientOwner)
            <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
                <h3 class="font-black text-gray-900 dark:text-white">Organisation information</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="organisation_name" class="text-sm font-semibold">Organisation name <span class="text-red-600">*</span></label>
                        <x-text-input id="organisation_name" name="organisation_name" type="text" class="mt-1 block w-full" :value="old('organisation_name', $user->organisation_name)" required autocomplete="organization" />
                        <x-input-error class="mt-2" :messages="$errors->get('organisation_name')" />
                    </div>
                    <div>
                        <label for="job_title" class="text-sm font-semibold">Job title / designation <span class="text-red-600">*</span></label>
                        <x-text-input id="job_title" name="job_title" type="text" class="mt-1 block w-full" :value="old('job_title', $user->job_title)" required autocomplete="organization-title" />
                        <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
                <h3 class="font-black text-gray-900 dark:text-white">Residential / correspondence address</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="address" class="text-sm font-semibold">Address line <span class="text-red-600">*</span></label>
                        <textarea id="address" name="address" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">{{ old('address', $user->address) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('address')" />
                    </div>
                    @foreach(['city'=>'City','state'=>'State / province','postal_code'=>'Postal code','country'=>'Country'] as $field=>$label)
                        <div>
                            <label for="{{ $field }}" class="text-sm font-semibold">{{ $label }} <span class="text-red-600">*</span></label>
                            <x-text-input :id="$field" :name="$field" type="text" class="mt-1 block w-full" :value="old($field, $user->{$field})" required />
                            <x-input-error class="mt-2" :messages="$errors->get($field)" />
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($user->role === 'student')
            <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
                <label for="address" class="text-sm font-semibold">Residential address <span class="text-red-600">*</span></label>
                <textarea id="address" name="address" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">{{ old('address', $user->address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20">
                <h3 class="font-black">Emergency contact</h3>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div><label class="text-sm font-semibold">Contact name <span class="text-red-600">*</span></label><x-text-input name="emergency_contact_name" type="text" class="mt-1 block w-full" :value="old('emergency_contact_name', $user->emergency_contact_name)" required /></div>
                    <div><label class="text-sm font-semibold">Contact phone <span class="text-red-600">*</span></label><x-text-input name="emergency_contact_phone" type="tel" class="mt-1 block w-full" :value="old('emergency_contact_phone', $user->emergency_contact_phone)" required /></div>
                </div>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ $isClientOwner ? 'Save and Continue' : __('Save') }}</x-primary-button>
            @if(session('status') === 'profile-updated')<p class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>@endif
        </div>
    </form>
</section>
