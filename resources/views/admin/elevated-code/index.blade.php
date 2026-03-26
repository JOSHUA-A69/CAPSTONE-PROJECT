<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="p-2 -ml-2 text-gray-500 hover:text-violet-600 dark:text-gray-400 dark:hover:text-violet-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="text-heading font-semibold text-xl leading-tight">
                Manage Elevated Registration Code
            </h2>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl flex items-center gap-3 animate-fade-in">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Main Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Header -->
                <div class="px-6 sm:px-8 py-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-violet-100 dark:bg-violet-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Elevated Registration Code</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Manage access code for privileged role registrations</p>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 sm:p-8">
                    <!-- Current Code Section -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Current Code
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="current_code_display" 
                                   value="{{ $currentCode }}" 
                                   readonly
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg font-mono text-base tracking-wider text-gray-800 dark:text-gray-200 pr-12 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all" />
                            <button type="button" 
                                    onclick="toggleCurrentCode()"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                                <svg id="currentEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="currentEyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Required for Admin, Staff, Adviser, and Priest registrations
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="relative mb-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-white dark:bg-gray-800 px-4 text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Update Code</span>
                        </div>
                    </div>

                    <!-- Update Form -->
                    <form method="POST" action="{{ route('admin.elevated-code.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- New Code Input -->
                        <div class="mb-6">
                            <label for="elevated_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                New Elevated Code
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="elevated_code" 
                                       name="elevated_code" 
                                       autocomplete="off"
                                       placeholder="Enter new code (min 6 characters)"
                                       class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg font-mono text-base tracking-wider text-gray-800 dark:text-gray-200 pr-12 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all placeholder:text-gray-400 placeholder:font-sans placeholder:tracking-normal @error('elevated_code') border-red-500 @enderror" />
                                <button type="button" 
                                        onclick="toggleNewCode()"
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                                    <svg id="newEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="newEyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('elevated_code')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Letters, numbers, underscores, or hyphens • Minimum 6 characters
                            </p>
                        </div>

                        <!-- Security Warning -->
                        <div class="mb-8 p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-xl">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-amber-800 dark:text-amber-200 text-sm">Security Notice</h4>
                                    <p class="text-sm text-amber-700 dark:text-amber-300/80 mt-1 leading-relaxed">
                                        Only share this code with authorized personnel. Changing the code will invalidate the previous one immediately.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Updated Submit Button - Green Gradient, Visible Text -->
<button type="submit" class="w-full inline-flex items-center justify-center gap-2.5 px-6 py-4 text-base font-semibold text-white bg-gradient-to-r from-emerald-500 via-green-500 to-emerald-600 hover:from-emerald-600 hover:via-green-600 hover:to-emerald-700 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/30 transition-all duration-200 shadow-md hover:shadow-lg active:scale-95">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
    </svg>
    Update Elevated Code
</button>

                    </form>
                </div>
            </div>

            <!-- Help Text -->
            <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-6">
                Changes take effect immediately for all new registrations
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleCurrentCode() {
            const input = document.getElementById('current_code_display');
            const eyeIcon = document.getElementById('currentEyeIcon');
            const eyeOffIcon = document.getElementById('currentEyeOffIcon');
            
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

        function toggleNewCode() {
            const input = document.getElementById('elevated_code');
            const eyeIcon = document.getElementById('newEyeIcon');
            const eyeOffIcon = document.getElementById('newEyeOffIcon');
            
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
</x-app-layout>
