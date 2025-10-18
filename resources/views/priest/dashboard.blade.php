<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Priest Dashboard</h2>

            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Welcome Message -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';

                        // Get pending confirmations count
                        $pendingCount = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->where('status', 'pending_priest_confirmation')
                            ->count();

                        // Get upcoming confirmed services
                        $upcomingCount = \App\Models\Reservation::where('officiant_id', $user->id)
                            ->where('priest_confirmation', 'confirmed')
                            ->where('schedule_date', '>=', now())
                            ->count();
                    @endphp

                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $displayName }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Manage your service assignments and schedule.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Pending Confirmations -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-1">Pending Confirmations</p>
                                <p class="text-4xl font-bold text-yellow-900 dark:text-yellow-200">{{ $pendingCount }}</p>
                            </div>
                            <div class="p-3 bg-yellow-200 dark:bg-yellow-700 rounded-full">
                                <svg class="w-8 h-8 text-yellow-700 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        @if($pendingCount > 0)
                        <a href="{{ route('priest.reservations.index', ['status' => 'pending_priest_confirmation']) }}"
                           class="mt-4 inline-flex items-center text-sm font-semibold text-yellow-700 dark:text-yellow-300 hover:underline">
                            View Pending Assignments
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        @else
                        <p class="mt-4 text-sm text-yellow-700 dark:text-yellow-300">No pending confirmations</p>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Services -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-green-200 dark:border-green-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-1">Upcoming Services</p>
                                <p class="text-4xl font-bold text-green-900 dark:text-green-200">{{ $upcomingCount }}</p>
                            </div>
                            <div class="p-3 bg-green-200 dark:bg-green-700 rounded-full">
                                <svg class="w-8 h-8 text-green-700 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('priest.reservations.index', ['time' => 'upcoming']) }}"
                           class="mt-4 inline-flex items-center text-sm font-semibold text-green-700 dark:text-green-300 hover:underline">
                            View Schedule
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('priest.reservations.index') }}"
                   class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">All Assignments</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">View all reservations</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.calendar') }}"
                   class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">Calendar View</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">See your schedule</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.index', ['time' => 'past']) }}"
                   class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full mr-4">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">Past Services</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">View history</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('priest.reservations.declined') }}"
                   class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow border-2 border-red-200 dark:border-red-800">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">Declined Services</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">View declined history</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
