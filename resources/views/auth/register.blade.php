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
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-900 dark:to-gray-800 p-6 rounded-2xl border-2 border-green-200 dark:border-green-900/30 shadow-sm">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-11 h-11 bg-gradient-to-br from-[#2ecc71] to-[#27ae60] rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Personal Information</h3>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tell us about yourself</p>
                </div>

                <!-- Name Fields -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="validation-field">
                        <x-input-label for="first_name" :value="__('First Name')" />
                        <div class="relative mt-1">
                            <x-text-input
                                id="first_name"
                                name="first_name"
                                type="text"
                                :value="old('first_name')"
                                required
                                autofocus
                                autocomplete="given-name"
                                placeholder="Enter your first name"
                                class="pr-10 transition-all duration-200" />
                            <div class="validation-icon absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="valid-icon w-5 h-5 text-green-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg class="invalid-icon w-5 h-5 text-red-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <p class="validation-error text-sm text-red-600 mt-1.5 hidden flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="error-message"></span>
                        </p>
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
                            placeholder="Optional"
                            class="mt-1" />
                        <p class="form-helper">Optional</p>
                        <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
                    </div>

                    <div class="validation-field">
                        <x-input-label for="last_name" :value="__('Last Name')" />
                        <div class="relative mt-1">
                            <x-text-input
                                id="last_name"
                                name="last_name"
                                type="text"
                                :value="old('last_name')"
                                required
                                autocomplete="family-name"
                                placeholder="Enter your last name"
                                class="pr-10 transition-all duration-200" />
                            <div class="validation-icon absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="valid-icon w-5 h-5 text-green-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg class="invalid-icon w-5 h-5 text-red-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <p class="validation-error text-sm text-red-600 mt-1.5 hidden flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="error-message"></span>
                        </p>
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-gray-800 p-6 rounded-2xl border-2 border-blue-200 dark:border-blue-900/30 shadow-sm">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Contact Information</h3>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">How can we reach you?</p>
                </div>

                <!-- Contact Fields -->
                <div class="space-y-5">
                    <!-- Email -->
                    <div class="validation-field">
                        <x-input-label for="email" :value="__('Email Address')" />
                        <div class="relative mt-1">
                            <x-text-input
                                id="email"
                                name="email"
                                type="email"
                                :value="old('email')"
                                required
                                autocomplete="username"
                                placeholder="your.email@example.com"
                                class="pr-10 transition-all duration-200" />
                            <!-- Validation Icon -->
                            <div class="validation-icon absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="valid-icon w-5 h-5 text-green-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg class="invalid-icon w-5 h-5 text-red-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <p class="validation-error text-sm text-red-600 mt-1.5 hidden flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="error-message"></span>
                        </p>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Phone -->
                    <div class="validation-field">
                        <x-input-label for="phone" :value="__('Phone Number')" />
                        <div class="relative mt-1">
                            <x-text-input
                                id="phone"
                                name="phone"
                                type="tel"
                                :value="old('phone')"
                                required
                                autocomplete="tel"
                                placeholder="09123456789"
                                maxlength="11"
                                class="pr-10 transition-all duration-200" />
                            <!-- Validation Icon -->
                            <div class="validation-icon absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="valid-icon w-5 h-5 text-green-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg class="invalid-icon w-5 h-5 text-red-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <p class="validation-error text-sm text-red-600 mt-1.5 hidden flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="error-message"></span>
                        </p>
                        <p class="form-helper text-gray-500">Philippine format: 10-11 digits (e.g., 09123456789)</p>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Security Section -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-gray-900 dark:to-gray-800 p-6 rounded-2xl border-2 border-purple-200 dark:border-purple-900/30 shadow-sm">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-11 h-11 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Security</h3>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Keep your account safe</p>
                </div>

                <!-- Password Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="validation-field">
                        <x-input-label for="password" :value="__('Password')" />
                        <div class="relative mt-1">
                            <x-text-input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="pr-20 transition-all duration-200" />
                            <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-3">
                                <div class="validation-icon pointer-events-none">
                                    <svg class="valid-icon w-5 h-5 text-green-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <svg class="invalid-icon w-5 h-5 text-red-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <button type="button"
                                        onclick="togglePassword('password', 'eyeIcon1', 'eyeOffIcon1')"
                                        class="text-gray-500 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400 transition-colors focus:outline-none"
                                        aria-label="Toggle password visibility">
                                    <svg id="eyeIcon1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="eyeOffIcon1" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="validation-error text-sm text-red-600 mt-1.5 hidden flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="error-message"></span>
                        </p>
                        <p class="form-helper">Minimum 8 characters</p>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="validation-field">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <div class="relative mt-1">
                            <x-text-input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="pr-20 transition-all duration-200" />
                            <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-3">
                                <div class="validation-icon pointer-events-none">
                                    <svg class="valid-icon w-5 h-5 text-green-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <svg class="invalid-icon w-5 h-5 text-red-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <button type="button"
                                        onclick="togglePassword('password_confirmation', 'eyeIcon2', 'eyeOffIcon2')"
                                        class="text-gray-500 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400 transition-colors focus:outline-none"
                                        aria-label="Toggle password visibility">
                                    <svg id="eyeIcon2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="eyeOffIcon2" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="validation-error text-sm text-red-600 mt-1.5 hidden flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="error-message"></span>
                        </p>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>
            </div>

            @if(config('registration.allow_role_selection'))
                <!-- Role Selection Section -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-gray-900 dark:to-gray-800 p-6 rounded-2xl border-2 border-amber-200 dark:border-amber-900/30 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-11 h-11 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Account Role</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Optional - Select if you have elevated access</p>
                        </div>
                    </div>

                <div class="space-y-5">
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
                    <div class="relative mt-1">
                        <input
                            id="elevated_code"
                            name="elevated_code"
                            type="password"
                            autocomplete="off"
                            class="form-input pr-12"
                            placeholder="••••••••••"
                            aria-describedby="elevated_code_help" />
                        <button type="button"
                                onclick="togglePassword('elevated_code', 'eyeIcon3', 'eyeOffIcon3')"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 transition-colors focus:outline-none"
                                aria-label="Toggle code visibility">
                            <svg id="eyeIcon3" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eyeOffIcon3" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <p class="form-helper" id="elevated_code_help">Required for elevated roles (Admin, Staff, Adviser, Priest) - Contact admin for access code</p>
                    <x-input-error :messages="$errors->get('elevated_code')" class="mt-2" />
                </div>
                </div>
                </div>
            @else
                <!-- Self-registration is only for Requestors -->
                <input type="hidden" name="role" value="requestor" />
            @endif

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit"
                        id="submitBtn"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-4 text-base font-bold text-white bg-[#2ecc71] rounded-lg transition-all duration-200 shadow-lg opacity-50 cursor-not-allowed">
                    <svg id="submitIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <svg id="loadingSpinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="submitText" class="tracking-wide">CREATE ACCOUNT</span>
                    <svg id="submitArrow" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    @push('styles')
    <style>
        /* Validation States */
        .validation-field input.is-valid {
            border-color: #22c55e !important;
            background-color: rgba(34, 197, 94, 0.05);
        }
        .validation-field input.is-invalid {
            border-color: #ef4444 !important;
            background-color: rgba(239, 68, 68, 0.05);
        }
        .validation-field input.is-invalid:focus {
            --tw-ring-color: rgba(239, 68, 68, 0.3);
            border-color: #ef4444 !important;
        }
        .validation-field input.is-valid:focus {
            --tw-ring-color: rgba(34, 197, 94, 0.3);
            border-color: #22c55e !important;
        }

        /* Shake Animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }
        .shake {
            animation: shake 0.5s ease-in-out;
        }

        /* Fade In Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .validation-error:not(.hidden) {
            animation: fadeIn 0.2s ease-out;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Toggle Password Visibility
        function togglePassword(inputId, eyeIconId, eyeOffIconId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById(eyeIconId);
            const eyeOffIcon = document.getElementById(eyeOffIconId);

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }

        // Validation State Tracker
        const validationState = {
            first_name: false,
            last_name: false,
            email: false,
            phone: false,
            password: false,
            password_confirmation: false,
            elevated_code: true // Default to true, will be set to false only if required and invalid
        };

        // Validation Rules
        const validators = {
            first_name: {
                validate: (value) => value.trim().length >= 2,
                message: 'First name must be at least 2 characters'
            },
            last_name: {
                validate: (value) => value.trim().length >= 2,
                message: 'Last name must be at least 2 characters'
            },
            email: {
                validate: (value) => {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(value.trim());
                },
                message: 'Please enter a valid email address'
            },
            phone: {
                validate: (value) => {
                    const digits = value.replace(/\D/g, '');
                    return digits.length >= 10 && digits.length <= 11;
                },
                message: 'Phone must be 10-11 digits (e.g., 09123456789)'
            },
            password: {
                validate: (value) => value.length >= 8,
                message: 'Password must be at least 8 characters'
            },
            password_confirmation: {
                validate: (value) => {
                    const password = document.getElementById('password').value;
                    return value.length >= 8 && value === password;
                },
                message: 'Passwords do not match'
            },
            elevated_code: {
                validate: (value) => {
                    const role = document.getElementById('role').value;
                    const elevatedRoles = ['admin', 'staff', 'adviser', 'priest'];

                    // If it's an elevated role, code is required and must be at least 6 characters
                    if (elevatedRoles.includes(role)) {
                        return value.trim().length >= 6;
                    }

                    // If it's not an elevated role, code is optional
                    return true;
                },
                message: 'Elevated code is required for elevated roles and must be at least 6 characters'
            }
        };

        // Show Validation State
        function showValidation(fieldId, isValid, message = '') {
            const field = document.getElementById(fieldId);
            const container = field.closest('.validation-field');
            if (!container) return;

            const input = container.querySelector('input') || container.querySelector('select');
            const validIcon = container.querySelector('.valid-icon');
            const invalidIcon = container.querySelector('.invalid-icon');
            const errorEl = container.querySelector('.validation-error');
            const errorMsg = container.querySelector('.error-message');

            // Reset classes
            input.classList.remove('is-valid', 'is-invalid');
            validIcon?.classList.add('hidden');
            invalidIcon?.classList.add('hidden');
            errorEl?.classList.add('hidden');

            if (isValid) {
                input.classList.add('is-valid');
                validIcon?.classList.remove('hidden');
                validationState[fieldId] = true;
            } else if (message) {
                input.classList.add('is-invalid');
                invalidIcon?.classList.remove('hidden');
                if (errorEl && errorMsg) {
                    errorMsg.textContent = message;
                    errorEl.classList.remove('hidden');
                }
                // Add shake animation
                input.classList.add('shake');
                setTimeout(() => input.classList.remove('shake'), 500);
                validationState[fieldId] = false;
            }

            updateSubmitButton();
        }

        // Validate Single Field
        function validateField(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return;

            const value = field.value;
            const validator = validators[fieldId];

            if (!validator) return;

            // Special handling for elevated_code
            if (fieldId === 'elevated_code') {
                const role = document.getElementById('role').value;
                const elevatedRoles = ['admin', 'staff', 'adviser', 'priest'];

                if (!elevatedRoles.includes(role)) {
                    // Not an elevated role, so elevated code is not required
                    validationState[fieldId] = true;
                    return;
                }

                // For elevated roles, validate the code
                if (value.trim().length === 0) {
                    showValidation(fieldId, false, 'Elevated code is required for this role');
                    return;
                }
            } else {
                // For other fields, check if they're empty
                if (value.length === 0) {
                    showValidation(fieldId, false, 'This field is required');
                    return;
                }
            }

            if (validator.validate(value)) {
                showValidation(fieldId, true);
            } else {
                showValidation(fieldId, false, validator.message);
            }
        }

        // Update Submit Button State
        function updateSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');

            // Check if all required fields have values
            let requiredFields = ['first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation'];

            // Add elevated_code to required fields if an elevated role is selected
            const roleField = document.getElementById('role');
            const role = roleField ? roleField.value : '';
            const elevatedRoles = ['admin', 'staff', 'adviser', 'priest'];
            if (elevatedRoles.includes(role)) {
                requiredFields.push('elevated_code');
            }

            const allFilled = requiredFields.every(id => {
                const field = document.getElementById(id);
                return field && field.value.trim().length > 0;
            });

            // Enable button if all required fields are filled, regardless of validation icons
            // The form validation will handle errors on submit
            submitBtn.disabled = !allFilled;

            // Add visual feedback
            if (allFilled) {
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.add('hover:bg-[#27ae60]', 'hover:shadow-xl', 'hover:scale-[1.01]');
            } else {
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:bg-[#27ae60]', 'hover:shadow-xl', 'hover:scale-[1.01]');
            }
        }

        // Phone Number Formatting (strip non-digits on input)
        function formatPhoneInput(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            input.value = value;
        }

        // Initialize Validation Listeners
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');

            // Add blur validation to all fields
            Object.keys(validators).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    // Validate on blur
                    field.addEventListener('blur', () => validateField(fieldId));

                    // Validate on input (for real-time feedback after first blur)
                    field.addEventListener('input', () => {
                        if (field.classList.contains('is-invalid') || field.classList.contains('is-valid')) {
                            validateField(fieldId);
                        }
                        // Always update submit button when any field changes
                        updateSubmitButton();
                    });
                }
            });

            // Phone number formatting
            const phoneField = document.getElementById('phone');
            if (phoneField) {
                phoneField.addEventListener('input', function() {
                    formatPhoneInput(this);
                    updateSubmitButton();
                });
            }

            // Password confirmation - also validate when password changes
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.addEventListener('input', () => {
                    const confirmField = document.getElementById('password_confirmation');
                    if (confirmField && (confirmField.classList.contains('is-invalid') || confirmField.classList.contains('is-valid'))) {
                        validateField('password_confirmation');
                    }
                    updateSubmitButton();
                });
            }

            // Role selection - validate elevated code when role changes
            const roleField = document.getElementById('role');
            if (roleField) {
                roleField.addEventListener('change', () => {
                    const elevatedCodeField = document.getElementById('elevated_code');
                    const role = roleField.value;
                    const elevatedRoles = ['admin', 'staff', 'adviser', 'priest'];

                    if (elevatedCodeField) {
                        // Clear any existing validation state
                        elevatedCodeField.classList.remove('is-valid', 'is-invalid');

                        // If not an elevated role, reset to valid state
                        if (!elevatedRoles.includes(role)) {
                            validationState['elevated_code'] = true;
                        } else {
                            // If elevated role, validate the current value
                            validateField('elevated_code');
                        }
                    }

                    updateSubmitButton();
                });
            }

            // Form Submit Handler
            form.addEventListener('submit', function(e) {
                // Validate all fields before submit
                let hasError = false;
                let requiredFields = ['first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation'];

                // Add elevated_code to required fields if an elevated role is selected
                const roleField = document.getElementById('role');
                const role = roleField ? roleField.value : '';
                const elevatedRoles = ['admin', 'staff', 'adviser', 'priest'];
                if (elevatedRoles.includes(role)) {
                    requiredFields.push('elevated_code');
                }

                // Validate required fields
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        validateField(fieldId);
                        if (!validationState[fieldId]) {
                            hasError = true;
                        }
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    // Scroll to first error
                    const firstError = document.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                    return;
                }

                // Show loading state
                const submitBtn = document.getElementById('submitBtn');
                const submitIcon = document.getElementById('submitIcon');
                const loadingSpinner = document.getElementById('loadingSpinner');
                const submitText = document.getElementById('submitText');
                const submitArrow = document.getElementById('submitArrow');

                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:bg-[#27ae60]', 'hover:shadow-xl', 'hover:scale-[1.01]');
                submitIcon.classList.add('hidden');
                submitArrow.classList.add('hidden');
                loadingSpinner.classList.remove('hidden');
                submitText.textContent = 'CREATING ACCOUNT...';
            });

            // Always reset button from loading state back to normal
            function resetButtonFromLoadingState() {
                const submitBtn = document.getElementById('submitBtn');
                const submitIcon = document.getElementById('submitIcon');
                const loadingSpinner = document.getElementById('loadingSpinner');
                const submitText = document.getElementById('submitText');
                const submitArrow = document.getElementById('submitArrow');

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitIcon?.classList.remove('hidden');
                    loadingSpinner?.classList.add('hidden');
                    submitArrow?.classList.remove('hidden');
                    if (submitText) {
                        submitText.textContent = 'CREATE ACCOUNT';
                    }
                }
            }

            // Handle server-side elevated code errors
            const elevatedCodeField = document.getElementById('elevated_code');
            if (elevatedCodeField) {
                // Find server-side error rendered by x-input-error
                const serverErrorEl = elevatedCodeField.closest('div')?.parentElement?.querySelector('.mt-2:last-child');

                elevatedCodeField.addEventListener('input', () => {
                    // Hide server-side error message when user starts typing
                    if (serverErrorEl && serverErrorEl.textContent.trim().length > 0) {
                        serverErrorEl.style.display = 'none';
                    }
                    validateField('elevated_code');
                    updateSubmitButton();
                });
            }

            // Reset button state immediately on page load (handles full page reload after server error)
            resetButtonFromLoadingState();

            // Also handle browser back-forward cache (bfcache) restoration
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    resetButtonFromLoadingState();
                    updateSubmitButton();
                }
            });

            // Initial button state - enable if there are pre-filled values (from old input)
            setTimeout(() => {
                // Initialize validation states for pre-filled or empty fields
                const fields = ['first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation', 'role'];
                fields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && field.value.trim()) {
                        // If field has value, validate it
                        validateField(fieldId);
                    }
                });

                // Also check agreement checkbox
                const agreementField = document.getElementById('agreement');
                if (agreementField && agreementField.checked) {
                    validationState.agreement = true;
                }

                updateSubmitButton();
            }, 100);
        });
    </script>
    @endpush
</x-guest-layout>
