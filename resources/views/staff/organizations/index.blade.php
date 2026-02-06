<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manage Organizations
            </h2>

            <div class="flex flex-wrap gap-2">
                <!-- Back Button -->
                <a href="{{ route('staff.dashboard') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-wide hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 transition duration-150">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>

                <!-- Archives Button -->
                <a href="{{ route('staff.organizations.archives') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-purple-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-wide hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-1 transition duration-150">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    Archives
                </a>

                <!-- Create New Organization Button -->
                <a href="{{ route('staff.organizations.create') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-emerald-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-wide hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 transition duration-150">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Organization
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <!-- Success Messages -->
            @if (session('status') === 'organization-created')
                <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 dark:border-emerald-400 rounded-lg shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-emerald-700 dark:text-emerald-200 font-medium text-sm">Organization created successfully!</span>
                    </div>
                </div>
            @endif

            @if (session('status') === 'organization-updated')
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 dark:border-blue-400 rounded-lg shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-blue-700 dark:text-blue-200 font-medium text-sm">Organization updated successfully!</span>
                    </div>
                </div>
            @endif

            @if (session('status') === 'organization-archived')
                <div class="mb-4 p-3 bg-orange-50 dark:bg-orange-900/20 border-l-4 border-orange-500 dark:border-orange-400 rounded-lg shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path>
                            <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-orange-700 dark:text-orange-200 font-medium text-sm">Organization archived successfully!</span>
                    </div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 dark:border-blue-400 rounded-lg shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-blue-700 dark:text-blue-200 font-medium text-sm">{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            <!-- Organizations Container -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Header -->
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">All Organizations</h3>
                </div>

                @if($organizations->count() > 0)
                    <!-- Organizations List -->
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($organizations as $organization)
                            <!-- Organization Card -->
                            <div class="p-4 sm:p-6 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <!-- Organization Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-2">
                                            <h4 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                {{ $organization->org_name }}
                                            </h4>
                                        </div>

                                        <!-- Description -->
                                        @if($organization->org_desc)
                                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-3 leading-relaxed">
                                                {{ Str::limit($organization->org_desc, 120) }}
                                            </p>
                                        @else
                                            <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mb-3 italic">
                                                No description provided
                                            </p>
                                        @endif

                                        <!-- Adviser Info -->
                                        <div class="flex items-center gap-1.5">
                                            @if($organization->adviser)
                                                <svg class="w-3 h-3 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <div class="min-w-0">
                                                    <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $organization->adviser->full_name }}
                                                    </span>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $organization->adviser->email }}
                                                    </div>
                                                </div>
                                            @else
                                                <svg class="w-3 h-3 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-xs text-red-600 dark:text-red-400 font-medium">No adviser assigned</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2 mt-3 sm:mt-0 sm:ml-4 flex-shrink-0">
                                        <!-- Edit Button -->
                                        <a href="{{ route('staff.organizations.edit', $organization->org_id) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 bg-blue-600 border border-transparent rounded-lg font-medium text-xs text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition duration-150">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>

                                        <!-- Archive Button -->
                                        <form method="POST" 
                                              action="{{ route('staff.organizations.destroy', $organization->org_id) }}"
                                              onsubmit="event.preventDefault(); showArchiveModal(this, '{{ $organization->org_name }}', {{ $organization->org_id }});"
                                              id="archive-form-{{ $organization->org_id }}"
                                              class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-orange-600 border border-transparent rounded-lg font-medium text-xs text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-1 transition duration-150">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                </svg>
                                                Archive
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($organizations->hasPages())
                        <div class="px-4 sm:px-6 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                            {{ $organizations->links() }}
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12 px-4">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-2">No organizations</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first organization.</p>
                        <a href="{{ route('staff.organizations.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create Organization
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div id="archiveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300" onclick="closeArchiveModal()"></div>

        <!-- Modal panel -->
        <div class="relative bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all duration-300 max-w-sm w-full animate-fade-in">
            <!-- Icon and Title -->
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 px-6 pt-6 pb-4 border-b border-orange-200 dark:border-orange-700/50">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-lg bg-orange-600 dark:bg-orange-500">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">
                            Archive Organization?
                        </h3>
                        <p class="text-xs text-orange-600 dark:text-orange-400 font-medium mt-1">This action can be undone</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-2">
                    You are about to archive
                </p>
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg px-3 py-2 mb-4 border border-orange-200 dark:border-orange-700/30">
                    <p class="font-semibold text-gray-900 dark:text-white truncate" id="orgNameModal"></p>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    This organization will be moved to your archive. You can restore it at any time from the Archives section.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button"
                        onclick="closeArchiveModal()"
                        class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-medium text-sm hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 dark:focus:ring-offset-gray-900 transition-all duration-150 hover:shadow-md">
                    Cancel
                </button>
                <button type="button"
                        onclick="confirmArchive()"
                        class="px-6 py-2.5 rounded-lg border border-transparent bg-orange-600 dark:bg-orange-500 text-white font-medium text-sm hover:bg-orange-700 dark:hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-offset-gray-900 transition-all duration-150 hover:shadow-lg active:scale-95">
                    Archive Organization
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
            #archiveModal .animate-fade-in {
                animation: fadeIn 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            }
        }
    </style>

    <script>
        let currentForm = null;

        function showArchiveModal(form, orgName, orgId) {
            currentForm = form;
            document.getElementById('orgNameModal').textContent = orgName;
            document.getElementById('archiveModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeArchiveModal() {
            document.getElementById('archiveModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentForm = null;
        }

        function confirmArchive() {
            if (currentForm) {
                // Remove the onsubmit handler and submit the form
                currentForm.onsubmit = null;
                currentForm.submit();
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeArchiveModal();
            }
        });
    </script>
</x-app-layout>
