<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">CREaM Administrator Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Welcome Card -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                        $userCount = \App\Models\User::count();
                        $pendingApprovalCount = class_exists('App\\Models\\Reservation')
                            ? \App\Models\Reservation::where('status', 'adviser_approved')->count()
                            : 0;
                    @endphp

                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $displayName }}!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Oversee users and approvals across the system.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Pending Admin Approvals -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-1">Pending Admin Approvals</p>
                                <p class="text-4xl font-bold text-yellow-900 dark:text-yellow-200">{{ $pendingApprovalCount }}</p>
                            </div>
                            <div class="p-3 bg-yellow-200 dark:bg-yellow-700 rounded-full">
                                <svg class="w-8 h-8 text-yellow-700 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('admin.notifications.index') }}"
                           class="mt-4 inline-flex items-center text-sm font-semibold text-yellow-700 dark:text-yellow-300 hover:underline">
                            Review Notifications
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 overflow-hidden shadow-sm sm:rounded-lg border border-indigo-200 dark:border-indigo-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-800 dark:text-indigo-300 mb-1">Total Users</p>
                                <p class="text-4xl font-bold text-indigo-900 dark:text-indigo-200">{{ $userCount }}</p>
                            </div>
                            <div class="p-3 bg-indigo-200 dark:bg-indigo-700 rounded-full">
                                <svg class="w-8 h-8 text-indigo-700 dark:text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0V10M7 20V10m5 10V10"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('admin.users.index') }}"
                           class="mt-4 inline-flex items-center text-sm font-semibold text-indigo-700 dark:text-indigo-300 hover:underline">
                            Manage Users
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.users.index') }}"
                   class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">Manage User Accounts</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Create, edit, deactivate users</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.notifications.index') }}"
                   class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">Notifications</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Review latest system notices</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('profile.edit') }}"
                   class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-full mr-4">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.89 0 5.558.915 7.879 2.463M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">Your Profile</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Update profile and password</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
