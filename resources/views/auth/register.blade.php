<x-guest-layout>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col items-center text-center space-y-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="relative">
                <div class="w-24 h-24 bg-[#2ecc71] rounded-full flex items-center justify-center shadow-lg p-1">
                    <div class="w-full h-full bg-white dark:bg-gray-800 rounded-full flex items-center justify-center p-2">
                        <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-full h-full object-contain" />
                    </div>
                </div>
                <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-[#2ecc71] rounded-full flex items-center justify-center shadow-md">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Create Account
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-lg">
                    Join eReligiousServices and start booking spiritual services at Holy Name University's Center for Religious Education and Mission
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <!-- Personal Information Section -->
            <div class="bg-gray-50 dark:bg-gray-900 p-5 rounded-lg border-l-4 border-[#2ecc71]">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-[#2ecc71] rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Personal Information</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Tell us about yourself</p>
                    </div>
                </div>

            <!-- Name Fields -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
            </div>

            <!-- Contact Information Section -->
            <div class="bg-gray-50 dark:bg-gray-900 p-5 rounded-lg border-l-4 border-blue-500">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Contact Information</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">How can we reach you?</p>
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
                    required
                    autocomplete="tel"
                    placeholder="+63 912 345 6789"
                    class="mt-1" />
                <p class="form-helper">Required - for contact purposes</p>
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            </div>

            <!-- Security Section -->
            <div class="bg-gray-50 dark:bg-gray-900 p-5 rounded-lg border-l-4 border-purple-500">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Security</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Keep your account safe</p>
                    </div>
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
            </div>

            @if(config('registration.allow_role_selection'))
                <!-- Role Selection Section -->
                <div class="bg-gray-50 dark:bg-gray-900 p-5 rounded-lg border-l-4 border-amber-500">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 bg-amber-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Account Role</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Optional - Select if you have elevated access</p>
                        </div>
                    </div>

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
                </div>
            @else
                <!-- Self-registration is only for Requestors -->
                <input type="hidden" name="role" value="requestor" />
            @endif

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-4 text-base font-bold text-white bg-[#2ecc71] rounded-lg hover:bg-[#27ae60] transition-all duration-200 shadow-lg hover:shadow-xl hover:scale-[1.01] transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="tracking-wide">CREATE ACCOUNT</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>

            <!-- Login Link -->
            <div class="text-center pt-4 pb-2 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    {{ __('Already have an account?') }}
                </p>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-[#2ecc71] hover:text-[#27ae60] border-2 border-[#2ecc71] hover:border-[#27ae60] rounded-lg hover:bg-green-50 dark:hover:bg-gray-800 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    {{ __('Sign In') }}
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
