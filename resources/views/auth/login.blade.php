<x-guest-layout>
    {{-- Split-screen login layout --}}
    <div class="flex flex-col md:flex-row min-h-[600px] lg:min-h-[650px]">

        {{-- ==================== LEFT BRANDED PANEL (hidden on mobile) ==================== --}}
        <div class="hidden md:flex md:w-1/2 relative overflow-hidden flex-col justify-between p-8 lg:p-12 xl:p-16"
             style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 50%, #312e81 100%);">

            {{-- Decorative Background Elements --}}
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                {{-- Large circle --}}
                <div class="absolute -top-20 -left-20 w-80 h-80 bg-white/5 rounded-full"></div>
                {{-- Medium circle --}}
                <div class="absolute top-1/3 -right-16 w-64 h-64 bg-white/5 rounded-full"></div>
                {{-- Small circle --}}
                <div class="absolute bottom-20 left-10 w-40 h-40 bg-white/5 rounded-full"></div>
                {{-- Grid pattern --}}
                <div class="absolute inset-0 opacity-[0.03]"
                     style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 30px 30px;"></div>
                {{-- Diagonal lines --}}
                <svg class="absolute bottom-0 left-0 w-full h-48 opacity-[0.06]" viewBox="0 0 400 200" fill="none">
                    <line x1="0" y1="200" x2="400" y2="0" stroke="white" stroke-width="1"/>
                    <line x1="0" y1="200" x2="300" y2="0" stroke="white" stroke-width="1"/>
                    <line x1="0" y1="200" x2="200" y2="0" stroke="white" stroke-width="1"/>
                    <line x1="100" y1="200" x2="400" y2="0" stroke="white" stroke-width="1"/>
                    <line x1="200" y1="200" x2="400" y2="50" stroke="white" stroke-width="1"/>
                </svg>
            </div>

            {{-- Top: Logo --}}
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <img src="/images/ers-logo.png" alt="eReligiousServices"
                         class="w-12 h-12 lg:w-14 lg:h-14 object-contain drop-shadow-lg" />
                    <span class="text-white/90 font-semibold text-lg lg:text-xl tracking-tight">eReligiousServices</span>
                </div>
            </div>

            {{-- Center: Welcome Message --}}
            <div class="relative z-10 flex-1 flex flex-col justify-center -mt-8">
                <h2 class="text-3xl lg:text-4xl xl:text-5xl font-bold text-white leading-tight mb-4 lg:mb-6">
                    Welcome<br>Back! 👋
                </h2>
                <p class="text-indigo-100/80 text-sm lg:text-base leading-relaxed max-w-xs lg:max-w-sm">
                    Manage your religious services seamlessly. Book, track, and stay connected with your parish community.
                </p>
            </div>

            {{-- Bottom: Copyright --}}
            <div class="relative z-10">
                <p class="text-indigo-200/50 text-xs lg:text-sm">
                    &copy; {{ date('Y') }} eReligiousServices. All rights reserved.
                </p>
            </div>
        </div>

        {{-- ==================== RIGHT FORM PANEL ==================== --}}
        <div class="w-full md:w-1/2 flex items-center justify-center px-5 py-8 sm:px-8 sm:py-10 md:px-10 lg:px-16 xl:px-20 bg-white dark:bg-gray-900">
            <div class="w-full max-w-sm md:max-w-md">

                {{-- Mobile-only Logo --}}
                <div class="flex flex-col items-center mb-6 md:hidden">
                    <img src="/images/ers-logo.png" alt="eReligiousServices"
                         class="w-14 h-14 object-contain drop-shadow-md mb-3" />
                </div>

                {{-- Header --}}
                <div class="mb-6 lg:mb-8">
                    <h1 class="text-2xl sm:text-3xl lg:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                        Sign In
                    </h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __("Don't have an account?") }}
                        <a href="{{ route('register') }}"
                           class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold transition-colors duration-200 underline decoration-indigo-600/30 hover:decoration-indigo-600 underline-offset-2">
                            {{ __('Create Account') }}
                        </a>
                    </p>
                </div>

                <x-auth-session-status class="mb-5" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email Field --}}
                    <div>
                        <x-input-label for="email" :value="__('Email Address')"
                                       class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5" />
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
                            class="w-full px-4 py-3 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 placeholder-gray-400" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs" id="email-error" />
                    </div>

                    {{-- Password Field --}}
                    <div>
                        <x-input-label for="password" :value="__('Password')"
                                       class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5" />
                        <div class="relative">
                            <x-text-input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                aria-describedby="password-error"
                                placeholder="••••••••"
                                class="w-full px-4 py-3 pr-11 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 placeholder-gray-400" />
                            <button type="button"
                                    onclick="togglePassword('password', 'eyeIcon', 'eyeOffIcon')"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200 focus:outline-none"
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

                    {{-- Role (hidden) --}}
                    <input type="hidden" name="role" value="" />

                    {{-- Remember Me & Forgot Password --}}
                    <div class="flex items-center justify-between gap-2">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 transition-colors" />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors duration-200 select-none">
                                {{ __('Remember me') }}
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition-colors duration-200 whitespace-nowrap">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-1">
                        <x-primary-button class="w-full justify-center py-3 text-sm font-semibold rounded-xl">
                            {{ __('Sign In') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Widen the guest-content-box for split-screen login
        (function() {
            var box = document.querySelector('.guest-content-box');
            if (box) {
                box.style.maxWidth = '72rem';
                box.style.padding = '0';
                box.style.borderRadius = '1.5rem';
            }
            // Remove padding from the parent flex container on desktop
            var parent = box ? box.parentElement : null;
            if (parent) {
                parent.style.paddingTop = '1.5rem';
                parent.style.paddingBottom = '1.5rem';
            }
        })();

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

    <style>
        /* Override guest-content-box for login split-screen */
        .guest-content-box {
            max-width: 100% !important;
            padding: 0 !important;
        }
        @media (min-width: 768px) {
            .guest-content-box {
                max-width: 72rem !important;
            }
        }
    </style>
</x-guest-layout>
