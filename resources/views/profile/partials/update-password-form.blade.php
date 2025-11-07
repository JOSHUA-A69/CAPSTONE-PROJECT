<section class="card">
    <!-- Success Message at Top -->
    @if (session('status') === 'password-updated')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 5000)"
            class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 dark:border-green-400 rounded-lg shadow-lg">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                </svg>
                <div>
                    <p class="text-green-700 dark:text-green-200 font-semibold">{{ __('Password updated successfully!') }}</p>
                    <p class="text-sm text-green-600 dark:text-green-300 mt-1">Your password has been securely updated.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="card-header">
        <div class="flex items-start gap-3">
            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-heading">
                    {{ __('Update Password') }}
                </h2>
                <p class="mt-1 text-sm text-muted">
                    {{ __('Ensure your account is using a strong password to stay secure.') }}
                </p>
            </div>
        </div>
    </div>

    <div class="card-body">
        <form method="post" action="{{ route('password.update') }}" class="space-y-6" x-data="passwordForm()">
            @csrf
            @method('put')

            <!-- Current Password -->
            <div>
                <x-input-label for="update_password_current_password" :value="__('Current Password')" required />
                <div class="relative mt-1">
                    <x-text-input
                        id="update_password_current_password"
                        name="current_password"
                        type="password"
                        x-bind:type="showCurrentPassword ? 'text' : 'password'"
                        class="pr-10"
                        autocomplete="current-password"
                        required
                        placeholder="Enter your current password" />
                    <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none"
                        tabindex="-1">
                        <svg x-show="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg x-show="showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <!-- New Password -->
            <div>
                <x-input-label for="update_password_password" :value="__('New Password')" required />
                <div class="relative mt-1">
                    <x-text-input
                        id="update_password_password"
                        name="password"
                        type="password"
                        x-bind:type="showNewPassword ? 'text' : 'password'"
                        class="pr-10"
                        autocomplete="new-password"
                        required
                        @input="checkPasswordStrength($event.target.value); checkPasswordMatch()"
                        placeholder="Create a strong password" />
                    <button
                        type="button"
                        @click="showNewPassword = !showNewPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none"
                        tabindex="-1">
                        <svg x-show="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg x-show="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>

                <!-- Password Strength Meter -->
                <div class="mt-3" x-show="passwordStrength > 0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Password Strength:</span>
                        <span class="text-xs font-semibold" :class="{
                            'text-red-600 dark:text-red-400': passwordStrength === 1,
                            'text-orange-600 dark:text-orange-400': passwordStrength === 2,
                            'text-yellow-600 dark:text-yellow-400': passwordStrength === 3,
                            'text-green-600 dark:text-green-400': passwordStrength === 4
                        }" x-text="passwordStrengthText"></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="h-full transition-all duration-300 rounded-full" :style="`width: ${passwordStrength * 25}%`" :class="{
                            'bg-red-500': passwordStrength === 1,
                            'bg-orange-500': passwordStrength === 2,
                            'bg-yellow-500': passwordStrength === 3,
                            'bg-green-500': passwordStrength === 4
                        }"></div>
                    </div>
                </div>

                <!-- Password Requirements -->
                <div class="mt-3 space-y-2">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Password must contain:</p>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 text-xs" :class="requirements.length ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" x-show="requirements.length"></path>
                                <circle cx="12" cy="12" r="10" stroke-width="2" x-show="!requirements.length"></circle>
                            </svg>
                            <span>At least 8 characters</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs" :class="requirements.uppercase ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" x-show="requirements.uppercase"></path>
                                <circle cx="12" cy="12" r="10" stroke-width="2" x-show="!requirements.uppercase"></circle>
                            </svg>
                            <span>One uppercase letter (A-Z)</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs" :class="requirements.lowercase ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" x-show="requirements.lowercase"></path>
                                <circle cx="12" cy="12" r="10" stroke-width="2" x-show="!requirements.lowercase"></circle>
                            </svg>
                            <span>One lowercase letter (a-z)</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs" :class="requirements.number ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" x-show="requirements.number"></path>
                                <circle cx="12" cy="12" r="10" stroke-width="2" x-show="!requirements.number"></circle>
                            </svg>
                            <span>One number (0-9)</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs" :class="requirements.special ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" x-show="requirements.special"></path>
                                <circle cx="12" cy="12" r="10" stroke-width="2" x-show="!requirements.special"></circle>
                            </svg>
                            <span>One special character (!@#$%^&*)</span>
                        </div>
                    </div>
                </div>

                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm New Password')" required />
                <div class="relative mt-1">
                    <x-text-input
                        id="update_password_password_confirmation"
                        name="password_confirmation"
                        type="password"
                        x-bind:type="showConfirmPassword ? 'text' : 'password'"
                        class="pr-10"
                        autocomplete="new-password"
                        required
                        @input="checkPasswordMatch()"
                        placeholder="Re-enter your new password" />
                    <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none"
                        tabindex="-1">
                        <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>

                <!-- Password Match Indicator -->
                <div class="mt-2" x-show="passwordMatchMessage">
                    <div class="flex items-center gap-2 text-sm" :class="passwordsMatch ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="passwordsMatch">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!passwordsMatch" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span x-text="passwordMatchMessage"></span>
                    </div>
                </div>

                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Security Tips -->
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">Security Tips</h4>
                        <ul class="text-xs text-blue-800 dark:text-blue-300 space-y-1">
                            <li>• Use a unique password you don't use anywhere else</li>
                            <li>• Avoid using personal information in your password</li>
                            <li>• Consider using a password manager to generate and store strong passwords</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-primary-button>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    {{ __('Update Password') }}
                </x-primary-button>

                <button
                    type="button"
                    onclick="this.closest('form').reset()"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    {{ __('Cancel') }}
                </button>
            </div>
        </form>
    </div>
</section>

<script>
function passwordForm() {
    return {
        showCurrentPassword: false,
        showNewPassword: false,
        showConfirmPassword: false,
        passwordStrength: 0,
        passwordStrengthText: '',
        passwordsMatch: false,
        passwordMatchMessage: '',
        requirements: {
            length: false,
            uppercase: false,
            lowercase: false,
            number: false,
            special: false
        },

        checkPasswordStrength(password) {
            let strength = 0;
            
            // Check requirements
            this.requirements.length = password.length >= 8;
            this.requirements.uppercase = /[A-Z]/.test(password);
            this.requirements.lowercase = /[a-z]/.test(password);
            this.requirements.number = /[0-9]/.test(password);
            this.requirements.special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

            // Calculate strength
            if (this.requirements.length) strength++;
            if (this.requirements.uppercase) strength++;
            if (this.requirements.lowercase) strength++;
            if (this.requirements.number) strength++;
            if (this.requirements.special) strength++;

            // Adjust strength based on length
            if (password.length >= 12) strength = Math.min(strength + 1, 5);
            
            // Normalize to 1-4 scale
            if (strength <= 2) {
                this.passwordStrength = 1;
                this.passwordStrengthText = 'Weak';
            } else if (strength === 3) {
                this.passwordStrength = 2;
                this.passwordStrengthText = 'Fair';
            } else if (strength === 4) {
                this.passwordStrength = 3;
                this.passwordStrengthText = 'Good';
            } else if (strength >= 5) {
                this.passwordStrength = 4;
                this.passwordStrengthText = 'Strong';
            }
        },

        checkPasswordMatch() {
            const password = document.getElementById('update_password_password').value;
            const confirmation = document.getElementById('update_password_password_confirmation').value;

            if (confirmation.length === 0) {
                this.passwordMatchMessage = '';
                return;
            }

            if (password === confirmation) {
                this.passwordsMatch = true;
                this.passwordMatchMessage = 'Passwords match!';
            } else {
                this.passwordsMatch = false;
                this.passwordMatchMessage = 'Passwords do not match';
            }
        }
    }
}
</script>
