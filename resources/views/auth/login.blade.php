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
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    aria-describedby="password-error"
                    class="mt-1" />
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
</x-guest-layout>
