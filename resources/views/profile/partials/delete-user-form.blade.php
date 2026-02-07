<section class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-red-200 dark:border-red-900/50 overflow-hidden">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 px-6 py-5 border-b border-red-200 dark:border-red-800">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-red-800 dark:text-red-200">
                    {{ __('Delete Account') }}
                </h2>
                <p class="mt-1 text-sm text-red-700 dark:text-red-300 leading-relaxed">
                    {{ __('Permanently remove your account and all associated data from the system. This action cannot be reversed.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="p-6 space-y-6">
        <!-- Critical Warning -->
        <div class="bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="space-y-2">
                    <h3 class="font-semibold text-red-800 dark:text-red-200">
                        {{ __('Warning: This action is irreversible') }}
                    </h3>
                    <div class="text-sm text-red-700 dark:text-red-300 space-y-1">
                        <p>{{ __('Once deleted, the following will be permanently removed:') }}</p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>{{ __('All reservations and booking history') }}</li>
                            <li>{{ __('Personal profile information') }}</li>
                            <li>{{ __('Chat messages and communications') }}</li>
                            <li>{{ __('System logs and activity records') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Section -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            @if(auth()->check() && auth()->user()->role === 'admin')
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Ready to proceed?') }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Click the button below to start the account deletion process.') }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <button
                            type="button"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 text-white font-semibold text-sm rounded-lg shadow-sm hover:shadow-md focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ __('Delete Account') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">
                            {{ __('Access Restricted') }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ __('Only administrators can delete accounts. Please contact support if you need assistance with account deletion.') }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Compact Mobile-Optimized Modal -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-sm w-full mx-3 overflow-hidden">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <!-- Close Button -->
                <button type="button" x-on:click="$dispatch('close')" class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Compact Header -->
                <div class="text-center pt-5 pb-4 px-5">
                    <div class="mx-auto w-12 h-12 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ __('Confirm Account Deletion') }}
                    </h2>
                    <p class="text-xs sm:text-sm text-red-600 dark:text-red-400 font-medium mt-1">
                        {{ __('This action cannot be undone') }}
                    </p>
                </div>

                <!-- Compact Body -->
                <div class="px-5 pb-5 space-y-4">
                    <!-- Simple Warning -->
                    <div class="bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500 p-3 rounded-r-lg">
                        <p class="text-xs sm:text-sm text-red-800 dark:text-red-200 leading-snug">
                            {{ __('Once deleted, all resources and data will be permanently removed. Please enter your password to confirm.') }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-2">
                        <label for="password" class="block text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Confirm Password') }}
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="w-full pl-3.5 pr-10 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all"
                                placeholder="{{ __('Enter password') }}"
                                autofocus
                                required
                            />
                            <svg class="absolute right-2.5 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        @if($errors->userDeletion->get('password'))
                            <div class="text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/10 p-2 rounded-lg">
                                @foreach ($errors->userDeletion->get('password') as $error)
                                    {{ $error }}
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Compact Buttons -->
                    <div class="flex flex-col gap-2 pt-2">
                        <button
                            type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-sm rounded-lg shadow-md hover:shadow-lg transition-all"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ __('Delete Account') }}
                        </button>

                        <button
                            type="button"
                            x-on:click="$dispatch('close')"
                            class="w-full px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-modal>
</section>
