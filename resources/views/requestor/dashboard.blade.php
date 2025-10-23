<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Requestor Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                        $myPending = class_exists('App\\Models\\Reservation') ? \App\Models\Reservation::where('user_id', $user->id)->where('status', 'pending')->count() : 0;
                        $myUpcoming = class_exists('App\\Models\\Reservation') ? \App\Models\Reservation::where('user_id', $user->id)->whereIn('status', ['approved','admin_approved','adviser_approved'])->where('schedule_date', '>=', now())->count() : 0;
                    @endphp

                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $displayName }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Request and track services for your group or yourself.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-1">My Pending Requests</p>
                                <p class="text-4xl font-bold text-yellow-900 dark:text-yellow-200">{{ $myPending }}</p>
                            </div>
                            <div class="p-3 bg-yellow-200 dark:bg-yellow-700 rounded-full">
                                <svg class="w-8 h-8 text-yellow-700 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('requestor.reservations.index', ['status' => 'pending']) }}" class="mt-4 inline-flex items-center text-sm font-semibold text-yellow-700 dark:text-yellow-300 hover:underline">
                            View Pending
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-green-200 dark:border-green-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300 mb-1">My Upcoming</p>
                                <p class="text-4xl font-bold text-green-900 dark:text-green-200">{{ $myUpcoming }}</p>
                            </div>
                            <div class="p-3 bg-green-200 dark:bg-green-700 rounded-full">
                                <svg class="w-8 h-8 text-green-700 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('requestor.reservations.index', ['time' => 'upcoming']) }}" class="mt-4 inline-flex items-center text-sm font-semibold text-green-700 dark:text-green-300 hover:underline">
                            View Schedule
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('requestor.reservations.create') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">New Reservation</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Submit a request</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('requestor.reservations.index') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">My Reservations</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Track statuses</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('requestor.notifications.index') }}" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">Notifications</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Latest updates</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
