<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Users
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                Archived Users
            </h2>
        </div>
    </x-slot>

    <div class="py-3 sm:py-6">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <!-- Status Messages -->
            @if (session('status') === 'user-restored')
                <div class="mb-3 p-2 sm:p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-green-800 dark:text-green-200 font-medium">User account restored!</span>
                    </div>
                </div>
            @endif

            @if (session('status') === 'user-archived')
                <div class="mb-3 p-2 sm:p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-amber-800 dark:text-amber-200 font-medium">User account archived!</span>
                    </div>
                </div>
            @endif

            @if (session('status') === 'user-permanently-deleted')
                <div class="mb-3 p-2 sm:p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-red-800 dark:text-red-200 font-medium">User account deleted!</span>
                    </div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-3 p-2 sm:p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg text-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-blue-800 dark:text-blue-200 font-medium">{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Content Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <!-- Header -->
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10 bg-amber-100 dark:bg-amber-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Archived Users</h3>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Archived accounts</p>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-3 sm:p-6">
                    @if(isset($archivedUsers) && $archivedUsers->count() > 0)
                        <!-- Desktop Table View -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            User
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">
                                            Role
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">
                                            Archived
                                        </th>
                                        <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($archivedUsers as $user)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-4 sm:px-6 py-3 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-7 w-7 rounded-full bg-orange-100 dark:bg-orange-900/30 flex-shrink-0 flex items-center justify-center">
                                                        <svg class="h-3 w-3 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="ml-2 min-w-0">
                                                        <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $user->full_name }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 sm:px-6 py-3 whitespace-nowrap hidden sm:table-cell">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($user->role === 'admin') bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200
                                                    @elseif($user->role === 'staff') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200
                                                    @elseif($user->role === 'priest') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200
                                                    @elseif($user->role === 'adviser') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                                    @endif">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                            <td class="px-4 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                                {{ $user->deleted_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-1.5" x-data="{ showRestoreModal: false, showDeleteModal: false, deleteConfirmText: '' }">
                                                    <!-- Restore Button -->
                                                    <button @click="showRestoreModal = true"
                                                            class="inline-flex items-center px-2 sm:px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                        <span class="hidden sm:inline ml-1">Restore</span>
                                                    </button>

                                                    <!-- Delete Button -->
                                                    <button @click="showDeleteModal = true"
                                                            class="inline-flex items-center px-2 sm:px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        <span class="hidden sm:inline ml-1">Delete</span>
                                                    </button>

                                                    <!-- Delete Confirmation Modal -->
                                                    <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-3" style="display: none;">
                                                        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDeleteModal = false"></div>
                                                        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                            <!-- Header -->
                                                            <div class="px-5 py-4 bg-red-600 dark:bg-red-900 border-b border-red-700 dark:border-red-800">
                                                                <div class="flex items-center justify-center gap-2">
                                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                    <h3 class="text-lg font-bold text-white">Delete Account</h3>
                                                                </div>
                                                            </div>

                                                            <!-- Content Section -->
                                                            <div class="px-5 py-4 space-y-3">
                                                                <!-- Warning -->
                                                                <div class="p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-lg text-center">
                                                                    <p class="text-xs font-semibold text-red-700 dark:text-red-200 mb-1">Warning</p>
                                                                    <p class="text-xs text-red-700 dark:text-red-300 leading-relaxed">
                                                                        Permanently delete <strong>{{ $user->full_name }}</strong>. This action cannot be undone.
                                                                    </p>
                                                                </div>

                                                                <!-- Confirmation -->
                                                                <div class="text-center">
                                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Type <span class="font-mono font-semibold text-gray-900 dark:text-white">confirm</span> to proceed</p>
                                                                    <input type="text"
                                                                           x-model="deleteConfirmText"
                                                                           placeholder="confirm"
                                                                           class="w-full px-3 py-2 border transition-all rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm font-mono text-center"
                                                                           :class="deleteConfirmText === 'confirm' ? 'border-red-500 dark:border-red-600 ring-1 ring-red-200 dark:ring-red-900' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 dark:focus:border-blue-500'"
                                                                           @focus="$el.style.outline = 'none'" />
                                                                </div>
                                                            </div>

                                                            <!-- Footer Actions -->
                                                            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 flex gap-2">
                                                                <button @click="showDeleteModal = false; deleteConfirmText = ''"
                                                                        class="flex-1 px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded transition-all">
                                                                    Cancel
                                                                </button>
                                                                <form method="POST" action="{{ route('admin.users.force-destroy', $user->id) }}" class="flex-1">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                            :disabled="deleteConfirmText !== 'confirm'"
                                                                            class="w-full px-4 py-2 text-xs font-bold uppercase rounded transition-all"
                                                                            :class="deleteConfirmText === 'confirm' ? 'bg-red-600 hover:bg-red-700 text-white cursor-pointer' : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed opacity-60'" >
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Restore Modal -->
                                                    <div x-show="showRestoreModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-3" style="display: none;">
                                                        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showRestoreModal = false"></div>
                                                        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-sm">
                                                            <div class="p-4">
                                                                <div class="flex items-center mb-3">
                                                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
                                                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                                        </svg>
                                                                    </div>
                                                                    <h3 class="ml-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Restore User</h3>
                                                                </div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                                                                    Are you sure you want to restore <strong>{{ $user->full_name }}</strong>?
                                                                </p>
                                                                <div class="flex gap-2">
                                                                    <button @click="showRestoreModal = false" class="flex-1 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">
                                                                        Cancel
                                                                    </button>
                                                                    <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" class="flex-1">
                                                                        @csrf
                                                                        <button type="submit" class="w-full px-3 py-2 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors">
                                                                            Restore
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="sm:hidden space-y-3">
                            @foreach($archivedUsers as $user)
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 p-3" x-data="{ showRestoreModal: false, showDeleteModalMobile: false, deleteConfirmTextMobile: '' }">
                                    <!-- Card Header -->
                                    <div class="flex items-start justify-between gap-2 pb-2 border-b border-gray-200 dark:border-gray-600 mb-2">
                                        <div class="flex items-start gap-2 min-w-0 flex-1">
                                            <div class="h-7 w-7 rounded-full bg-orange-100 dark:bg-orange-900/30 flex-shrink-0 flex items-center justify-center mt-0.5">
                                                <svg class="h-3 w-3 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <h4 class="text-xs font-semibold text-gray-900 dark:text-white truncate">{{ $user->full_name }}</h4>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded whitespace-nowrap flex-shrink-0
                                            @if($user->role === 'admin') bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200
                                            @elseif($user->role === 'staff') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200
                                            @elseif($user->role === 'priest') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200
                                            @elseif($user->role === 'adviser') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                            @endif">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </div>

                                    <!-- Card Info -->
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                        Archived: {{ $user->deleted_at->format('M d, Y') }}
                                    </div>

                                    <!-- Card Actions -->
                                    <div class="flex gap-2">
                                        <button @click="showRestoreModal = true"
                                                class="flex-1 inline-flex items-center justify-center px-2 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Restore
                                        </button>
                                        <button @click="showDeleteModalMobile = true"
                                                class="flex-1 inline-flex items-center justify-center px-2 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete
                                        </button>

                                        <!-- Mobile Delete Confirmation Modal -->
                                        <div x-show="showDeleteModalMobile" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-3" style="display: none;">
                                            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDeleteModalMobile = false"></div>
                                            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                <!-- Header -->
                                                <div class="px-4 py-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border-b border-red-200 dark:border-red-800/50">
                                                    <div class="flex items-start gap-3">
                                                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete Account</h3>
                                                            <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">Permanent deletion</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Content -->
                                                <div class="px-4 py-4 space-y-3">
                                                    <!-- Warning -->
                                                    <div class="p-3 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-600 rounded-r-lg">
                                                        <p class="text-xs text-red-800 dark:text-red-200">
                                                            <span class="font-semibold">⚠️ Warning:</span> This cannot be undone.
                                                        </p>
                                                    </div>

                                                    <!-- User Info -->
                                                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 dark:border-blue-600 rounded-r-lg">
                                                        <p class="text-xs text-gray-700 dark:text-gray-300"><span class="font-semibold">User:</span></p>
                                                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ $user->full_name }}</p>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 truncate">{{ $user->email }}</p>
                                                    </div>

                                                    <!-- Confirmation Input -->
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        Type <span class="font-mono font-semibold">confirm</span> to delete:
                                                    </p>
                                                    <input type="text"
                                                           x-model="deleteConfirmTextMobile"
                                                           placeholder="Type 'confirm' to proceed"
                                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-xs focus:ring-2 focus:ring-red-500 focus:border-transparent" />
                                                </div>

                                                <!-- Footer -->
                                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex flex-col gap-2">
                                                    <button @click="showDeleteModalMobile = false; deleteConfirmTextMobile = ''"
                                                            class="w-full px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                                        Cancel
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.users.force-destroy', $user->id) }}" class="w-full">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                :disabled="deleteConfirmTextMobile !== 'confirm'"
                                                                :class="deleteConfirmTextMobile === 'confirm' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                                                class="w-full px-4 py-2 text-xs font-medium rounded-lg transition-all">
                                                            Delete Permanently
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Restore Modal for Mobile -->
                                    <div x-show="showRestoreModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-3" style="display: none;">
                                        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showRestoreModal = false"></div>
                                        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-sm">
                                            <div class="p-4">
                                                <div class="flex items-center mb-3">
                                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                    </div>
                                                    <h3 class="ml-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Restore User</h3>
                                                </div>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
                                                    Restore <strong class="text-gray-900 dark:text-gray-100">{{ $user->full_name }}</strong>?
                                                </p>
                                                <div class="flex gap-2">
                                                    <button @click="showRestoreModal = false" class="flex-1 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">
                                                        Cancel
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" class="flex-1">
                                                        @csrf
                                                        <button type="submit" class="w-full px-3 py-2 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded transition-colors">
                                                            Restore
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($archivedUsers->hasPages())
                            <div class="mt-4 sm:mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 sm:pt-6">
                                {{ $archivedUsers->links() }}
                            </div>
                        @endif
                    @elseif(isset($archivedUsers))
                        <!-- Empty State -->
                        <div class="text-center py-8 sm:py-12">
                            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-amber-100 dark:bg-amber-900/20 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">No archived users</h3>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-4">User accounts that you archive will appear here.</p>
                            <a href="{{ route('admin.users.index') }}"
                               class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-md transition-colors">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Users
                            </a>
                        </div>
                    @else
                        <!-- Migration Required State -->
                        <div class="text-center py-8 sm:py-12">
                            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-yellow-100 dark:bg-yellow-900/20 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Feature Not Available</h3>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-3">
                                Database migration required. Run:
                            </p>
                            <div class="mb-4 inline-block bg-gray-100 dark:bg-gray-700 rounded-md px-2 py-1 sm:px-3 sm:py-2">
                                <code class="text-xs sm:text-sm text-gray-800 dark:text-gray-300">php artisan migrate</code>
                            </div>
                            <div class="flex flex-col gap-2 justify-center">
                                <a href="{{ route('admin.users.index') }}"
                                   class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-medium rounded-md transition-colors">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Back to Users
                                </a>
                                <a href="{{ route('admin.dashboard') }}"
                                   class="inline-flex items-center justify-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs sm:text-sm font-medium rounded-md transition-colors">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    Dashboard
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
