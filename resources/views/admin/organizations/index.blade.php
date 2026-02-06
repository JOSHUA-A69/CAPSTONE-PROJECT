<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Organizations
            </h2>
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-xs sm:text-sm text-white hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 shadow-sm">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="whitespace-nowrap">Back to Dashboard</span>
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl rounded-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">All Organizations</h3>
                        <span class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-full text-xs sm:text-sm font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-300 dark:border-blue-700 flex-shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                            </svg>
                            <span class="whitespace-nowrap">{{ $organizations->count() }} Total</span>
                        </span>
                    </div>

                    @if($organizations->count() > 0)

                        <!-- Desktop View: Organizations Table -->
                        <div class="hidden lg:block">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Organization Name</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Adviser</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($organizations as $organization)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $organization->org_name }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-600 dark:text-gray-300 max-w-xs">{{ $organization->org_desc ?? '—' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($organization->adviser)
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $organization->adviser->full_name ?? $organization->adviser->name }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $organization->adviser->email }}</div>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                                            No adviser assigned
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mobile View: Organizations Cards -->
                        <div class="block lg:hidden space-y-3 sm:space-y-4">
                            @foreach($organizations as $index => $organization)
                                @php
                                    $gradients = [
                                        'bg-gradient-to-br from-blue-500 to-purple-600',
                                        'bg-gradient-to-br from-emerald-500 to-teal-600',
                                        'bg-gradient-to-br from-orange-500 to-red-600',
                                        'bg-gradient-to-br from-purple-500 to-pink-600',
                                        'bg-gradient-to-br from-indigo-500 to-blue-600',
                                        'bg-gradient-to-br from-green-500 to-emerald-600',
                                        'bg-gradient-to-br from-yellow-500 to-orange-600',
                                        'bg-gradient-to-br from-pink-500 to-rose-600'
                                    ];
                                    $gradient = $gradients[$index % count($gradients)];
                                @endphp

                                <div class="relative overflow-hidden rounded-xl sm:rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]">
                                    <!-- Gradient Header -->
                                    <div class="{{ $gradient }} p-4 sm:p-6 text-white">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0 pr-3">
                                                <h4 class="text-base sm:text-xl font-bold truncate mb-1">
                                                    {{ $organization->org_name }}
                                                </h4>
                                                <p class="text-white/80 text-xs sm:text-sm">
                                                    Organization
                                                </p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- White Content Area -->
                                    <div class="bg-white dark:bg-gray-800 p-4 sm:p-6">
                                        <!-- Description -->
                                        @if($organization->org_desc)
                                        <div class="mb-4 sm:mb-6">
                                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-sm sm:text-base">
                                                {{ $organization->org_desc }}
                                            </p>
                                        </div>
                                        @endif

                                        <!-- Adviser Section -->
                                        <div class="flex flex-col xs:flex-row xs:items-center xs:justify-between gap-3">
                                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                                @if($organization->adviser)
                                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                            {{ $organization->adviser->full_name ?? $organization->adviser->name }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                            {{ $organization->adviser->email }}
                                                        </p>
                                                    </div>
                                                @else
                                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs sm:text-sm font-medium text-red-600 dark:text-red-400">
                                                            No adviser assigned
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Status Badge -->
                                            <div class="flex-shrink-0">
                                                @if($organization->adviser)
                                                    <span class="inline-flex items-center px-2 py-1 sm:px-2.5 sm:py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                        <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-400 rounded-full mr-1 sm:mr-1.5 flex-shrink-0"></div>
                                                        <span class="whitespace-nowrap">Assigned</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 sm:px-2.5 sm:py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                                        <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-red-400 rounded-full mr-1 sm:mr-1.5 flex-shrink-0"></div>
                                                        <span class="whitespace-nowrap">Unassigned</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 sm:mt-6 border-t border-gray-200 dark:border-gray-600 pt-4">
                            {{ $organizations->links() }}
                        </div>
                    @else
                        <div class="text-center py-12 sm:py-16 px-4">
                            <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4 sm:mb-6">
                                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-2">No organizations found</h3>
                            <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 max-w-sm mx-auto">Organizations will appear here once they have been added to the system by staff members.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
