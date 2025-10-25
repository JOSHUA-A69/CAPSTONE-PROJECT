<x-guest-layout>
    <div class="p-4 lg:p-6">
        <div class="flex flex-col items-center mb-8">
            <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-24 h-24 mb-4 object-contain" />
            <h1 class="text-3xl font-bold text-heading">Create Account</h1>
            <p class="mt-2 text-sm text-muted">Join eReligiousServices today</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <!-- Name Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="first_name" :value="__('First Name')" />
                    <x-text-input
                        id="first_name"
                        name="first_name"
                        type="text"
                        :value="old('first_name')"
                        required
                        autofocus
                        autocomplete="given-name"
                        class="mt-1" />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="middle_name" :value="__('Middle Name')" />
                    <x-text-input
                        id="middle_name"
                        name="middle_name"
                        type="text"
                        :value="old('middle_name')"
                        autocomplete="additional-name"
                        class="mt-1" />
                    <p class="form-helper">Optional</p>
                    <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="last_name" :value="__('Last Name')" />
                    <x-text-input
                        id="last_name"
                        name="last_name"
                        type="text"
                        :value="old('last_name')"
                        required
                        autocomplete="family-name"
                        class="mt-1" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>
            </div>

            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    required
                    autocomplete="username"
                    class="mt-1" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Phone -->
            <div>
                <x-input-label for="phone" :value="__('Phone Number')" />
                <x-text-input
                    id="phone"
                    name="phone"
                    type="tel"
                    :value="old('phone')"
                    autocomplete="tel"
                    placeholder="+63 912 345 6789"
                    class="mt-1" />
                <p class="form-helper">Optional - for contact purposes</p>
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1" />
                    <p class="form-helper">Minimum 8 characters</p>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="mt-1" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>

            @if(config('registration.allow_role_selection'))
                <!-- Role Selection -->
                <div>
                    <x-input-label for="role" :value="__('User Role')" />
                    <select id="role" name="role" class="form-input mt-1">
                        <option value="">{{ __('Select your role') }}</option>
                        <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ old('role')=='staff' ? 'selected' : '' }}>Staff</option>
                        <option value="adviser" {{ old('role')=='adviser' ? 'selected' : '' }}>Adviser</option>
                        <option value="requestor" {{ old('role', 'requestor')=='requestor' ? 'selected' : '' }}>Requestor</option>
                        <option value="priest" {{ old('role')=='priest' ? 'selected' : '' }}>Priest</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <!-- Elevated Code -->
                <div>
                    <x-input-label for="elevated_code" :value="__('Elevated Registration Code')" />
                    <x-text-input
                        id="elevated_code"
                        name="elevated_code"
                        type="text"
                        :value="old('elevated_code')"
                        placeholder="Required for Admin/Staff/Priest roles"
                        class="mt-1" />
                    <p class="form-helper">Required for elevated roles (Admin, Staff, Adviser, Priest)</p>
                    <x-input-error :messages="$errors->get('elevated_code')" class="mt-2" />
                </div>
            @else
                <!-- Self-registration is only for Requestors -->
                <input type="hidden" name="role" value="requestor" />
            @endif

            <!-- Submit Button -->
            <div class="mt-8">
                <x-primary-button class="w-full justify-center btn-lg">
                    {{ __('Create Account') }}
                </x-primary-button>
            </div>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <span class="text-sm text-muted">{{ __('Already have an account?') }}</span>
                <a href="{{ route('login') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold ml-1 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded">
                    {{ __('Sign In') }}
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
                        Already registered? <a href="{{ route('login') }}" class="text-[#2ecc71] underline">Sign in</a>
                    </div>
                </form>
    </div>
</x-guest-layout>
