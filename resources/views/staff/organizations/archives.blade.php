<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0">
            <h2 class="font-bold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Archived Organizations
            </h2>

            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                <!-- Back to Organizations -->
                <a href="{{ route('staff.organizations.index') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Organizations
                </a>

                <!-- Back to Dashboard -->
                <a href="{{ route('staff.dashboard') }}"
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Info Message for Migration Required -->
            @if (session('info'))
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 dark:border-blue-400 rounded-lg shadow">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-blue-700 dark:text-blue-200 font-semibold">{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            <!-- Success Messages -->
            @if (session('status') === 'organization-restored')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 dark:border-green-400 rounded-lg shadow">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-green-700 dark:text-green-200 font-semibold">Organization restored successfully!</span>
                    </div>
                </div>
            @endif

            @if (session('status') === 'organization-permanently-deleted')
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-400 rounded-lg shadow">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-red-700 dark:text-red-200 font-semibold">Organization permanently deleted!</span>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Header Section -->
                <div class="px-4 sm:px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Archived Organizations</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Organizations that have been archived can be restored or permanently deleted</p>
                        </div>
                    </div>
                </div>

                @if(isset($archivedOrganizations) && $archivedOrganizations->count() > 0)
                    <!-- Organizations Grid -->
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($archivedOrganizations as $organization)
                            <div class="px-4 sm:px-6 py-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                    <!-- Organization Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="flex-shrink-0 w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $organization->org_name }}
                                                </h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Archived on {{ $organization->deleted_at->format('M d, Y') }} at {{ $organization->deleted_at->format('h:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        @if($organization->org_desc)
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 mb-4">
                                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                                    {{ $organization->org_desc }}
                                                </p>
                                            </div>
                                        @else
                                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 mb-4">
                                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">
                                                    No description provided
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex flex-col sm:flex-row gap-2 sm:ml-4">
                                        <!-- Restore Button -->
                                        <button type="button"
                                                onclick="showRestoreConfirmation('{{ addslashes($organization->org_name) }}', '{{ $organization->org_id }}', {{ $loop->index }});"
                                                class="inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150 shadow-sm w-full sm:w-auto">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Restore
                                        </button>

                                        <!-- Permanent Delete Button -->
                                        <button type="button"
                                                onclick="showDeleteConfirmation('{{ addslashes($organization->org_name) }}', '{{ $organization->org_id }}', {{ $loop->index }});"
                                                class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150 shadow-sm w-full sm:w-auto">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete Forever
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($archivedOrganizations->hasPages())
                        <div class="px-4 sm:px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                            {{ $archivedOrganizations->links() }}
                        </div>
                    @endif
                @elseif(isset($archivedOrganizations))
                    <!-- Empty State -->
                    <div class="text-center py-12 px-4">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-2">No archived organizations</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Organizations that you archive will appear here.</p>
                        <a href="{{ route('staff.organizations.index') }}"
                           class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Organizations
                        </a>
                    </div>
                    @else
                    <!-- Migration Required State -->
                    <div class="text-center py-12 px-4">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Archive Feature Not Available</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 max-w-md mx-auto">
                            The archive functionality requires a database migration.
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Please contact your system administrator or run: 
                            <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">php artisan migrate</code>
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="{{ route('staff.organizations.index') }}"
                               class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Organizations
                            </a>
                            <a href="{{ route('staff.dashboard') }}"
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Go to Dashboard
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div id="restoreConfirmationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" aria-labelledby="restore-modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300" onclick="closeRestoreConfirmation()"></div>

        <!-- Modal panel -->
        <div class="relative bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all duration-300 max-w-sm w-full animate-fade-in">
            <!-- Icon and Title -->
            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 px-6 pt-6 pb-4 border-b border-green-200 dark:border-green-700/50">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-lg bg-green-600 dark:bg-green-500">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="restore-modal-title">
                            Restore Organization?
                        </h3>
                        <p class="text-xs text-green-600 dark:text-green-400 font-medium mt-1">This will make it active again</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                    You are about to restore
                </p>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg px-3 py-2 mb-4 border border-green-200 dark:border-green-700/30">
                    <p class="font-semibold text-gray-900 dark:text-white truncate" id="restoreOrgName"></p>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    This organization will be restored and become active again. You can archive it again at any time if needed.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button"
                        onclick="closeRestoreConfirmation()"
                        class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium text-sm hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 dark:focus:ring-offset-gray-900 transition-all duration-150 hover:shadow-md">
                    Cancel
                </button>
                <form method="POST" id="restoreForm" action="#" style="display: none;">
                    @csrf
                </form>
                <button type="button"
                        onclick="submitRestoreForm()"
                        class="px-6 py-2.5 rounded-lg border border-transparent bg-green-600 dark:bg-green-500 text-white font-medium text-sm hover:bg-green-700 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-900 transition-all duration-150 hover:shadow-lg active:scale-95">
                    Restore Organization
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" aria-labelledby="delete-modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300" onclick="closeDeleteConfirmation()"></div>

        <!-- Modal panel -->
        <div class="relative bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all duration-300 max-w-sm w-full animate-fade-in">
            <!-- Icon and Title -->
            <div class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30 px-6 pt-6 pb-4 border-b border-red-200 dark:border-red-700/50">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-lg bg-red-600 dark:bg-red-500">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="delete-modal-title">
                            Delete Permanently?
                        </h3>
                        <p class="text-xs text-red-600 dark:text-red-400 font-medium mt-1">This cannot be undone</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                    You are about to permanently delete
                </p>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg px-3 py-2 mb-4 border border-red-200 dark:border-red-700/30">
                    <p class="font-semibold text-gray-900 dark:text-white truncate" id="deleteOrgName"></p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-200 dark:border-red-700/30 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-red-700 dark:text-red-200 font-medium">
                            This action cannot be reversed. The organization will be permanently removed.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button"
                        onclick="closeDeleteConfirmation()"
                        class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium text-sm hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 dark:focus:ring-offset-gray-900 transition-all duration-150 hover:shadow-md">
                    Cancel
                </button>
                <form method="POST" id="deleteForm" action="#" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
                <button type="button"
                        onclick="submitDeleteForm()"
                        class="px-6 py-2.5 rounded-lg border border-transparent bg-red-600 dark:bg-red-500 text-white font-medium text-sm hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900 transition-all duration-150 hover:shadow-lg active:scale-95">
                    Delete Forever
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (max-width: 640px) {
            .animate-fade-in {
                animation: fadeIn 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            }
        }
    </style>

    <script>
        // Restore Modal Functions
        function showRestoreConfirmation(orgName, orgId, index) {
            document.getElementById('restoreOrgName').textContent = orgName;
            document.getElementById('restoreForm').action = `/staff/organizations/${orgId}/restore`;
            document.getElementById('restoreConfirmationModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeRestoreConfirmation() {
            document.getElementById('restoreConfirmationModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function submitRestoreForm() {
            document.getElementById('restoreForm').submit();
        }

        // Delete Modal Functions
        function showDeleteConfirmation(orgName, orgId, index) {
            document.getElementById('deleteOrgName').textContent = orgName;
            document.getElementById('deleteForm').action = `/staff/organizations/${orgId}`;
            document.getElementById('deleteConfirmationModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteConfirmation() {
            document.getElementById('deleteConfirmationModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function submitDeleteForm() {
            document.getElementById('deleteForm').submit();
        }

        // Close modals when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            // Restore modal overlay close
            const restoreOverlay = document.getElementById('restoreConfirmationModal')?.querySelector('[onclick*="closeRestoreConfirmation"]');
            
            // Delete modal overlay close
            const deleteOverlay = document.getElementById('deleteConfirmationModal')?.querySelector('[onclick*="closeDeleteConfirmation"]');
        });
    </script>
</x-app-layout>
