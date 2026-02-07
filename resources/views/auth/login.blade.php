<x-guest-layout>
    <div class="flex items-center justify-center min-h-screen px-3 sm:px-3">
        <div class="w-full max-w-xs sm:max-w-sm px-5 sm:px-6 py-6 sm:py-8">
            <!-- Logo and Header -->
            <div class="flex flex-col items-center mb-5 sm:mb-6">
                <div class="relative mb-4 sm:mb-5 transform transition-transform duration-300 hover:scale-105">
                    <img src="/images/ers-logo.png" alt="eReligiousServices"
                         class="w-14 h-14 sm:w-16 sm:h-16 object-contain drop-shadow-md" />
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                    Sign In
                </h1>
                <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400 text-center leading-relaxed">
                    Welcome back! Please enter your credentials
                </p>
            </div>

            <x-auth-session-status class="mb-5 sm:mb-6" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-3.5 sm:space-y-4">
                @csrf

                <!-- Email Field -->
                <div class="space-y-2">
                    <x-input-label for="email" :value="__('Email Address')"
                                   class="text-sm font-medium text-gray-700 dark:text-gray-300" />
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                        aria-describedby="email-error"
                        placeholder="you@example.com"
                        class="w-full px-4 py-2.5 sm:py-3 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" id="email-error" />
                </div>

                <!-- Password Field -->
                <div class="space-y-2">
                    <x-input-label for="password" :value="__('Password')"
                                   class="text-sm font-medium text-gray-700 dark:text-gray-300" />
                    <div class="relative">
                        <x-text-input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            aria-describedby="password-error"
                            placeholder="••••••••"
                            class="w-full px-4 py-2.5 sm:py-3 pr-11 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200" />
                        <button type="button"
                                onclick="togglePassword('password', 'eyeIcon', 'eyeOffIcon')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200 focus:outline-none"
                                aria-label="Toggle password visibility">
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs" id="password-error" />
                </div>

                <!-- Role (hidden) -->
                <input type="hidden" name="role" value="" />

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between gap-2 pt-2">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-2 transition-colors" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors duration-200">
                            {{ __('Remember me') }}
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 rounded whitespace-nowrap">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <x-primary-button class="w-full justify-center py-2.5 sm:py-3 text-sm sm:text-base font-semibold">
                        {{ __('Sign In') }}
                    </x-primary-button>
                </div>

                <!-- Register Link -->
                <div class="pt-2 text-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __("Don't have an account?") }}</span>
                    <a href="{{ route('register') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold ml-1.5 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded inline">
                        {{ __('Create Account') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
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
    </script>
    @endpush
</x-guest-layout>
