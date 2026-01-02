<x-guest-layout>
    <div class="p-4 lg:p-6">
        <div class="flex flex-col items-center mb-8">
            <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-24 h-24 mb-4 object-contain" />
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Sign In</h1>
            <p class="mt-2 text-sm text-muted">Welcome back! Please enter your credentials</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Email Field -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    aria-describedby="email-error"
                    class="mt-1" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" id="email-error" />
            </div>

            <!-- Password Field -->
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <div class="relative mt-1">
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        aria-describedby="password-error"
                        class="pr-12" />
                    <button type="button" 
                            onclick="togglePassword('password', 'eyeIcon', 'eyeOffIcon')"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors focus:outline-none"
                            aria-label="Toggle password visibility">
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" id="password-error" />
            </div>

            <!-- Role (hidden) -->
            <input type="hidden" name="role" value="" />

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-2 transition-colors" />
                    <span class="ms-2 text-sm text-body group-hover:text-gray-900 dark:group-hover:text-gray-100 transition-colors">
                        {{ __('Remember me') }}
                    </span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <div class="mt-6">
                <x-primary-button class="w-full justify-center btn-lg">
                    {{ __('Sign In') }}
                </x-primary-button>
            </div>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <span class="text-sm text-muted">{{ __("Don't have an account?") }}</span>
                <a href="{{ route('register') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold ml-1 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded">
                    {{ __('Create Account') }}
                </a>
            </div>
        </form>
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
