<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Actions Bar -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex-1 w-full md:w-auto">
                    <!-- Search & Filter Form -->
                    <form method="GET" action="{{ route('admin.users.index') }}" id="userFilterForm" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-grow max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Search by name or email..."
                                   class="block w-full pl-10 pr-3 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors">
                        </div>

                        <div class="flex gap-2">
                            <select name="role"
                                    onchange="document.getElementById('userFilterForm').submit();"
                                    class="w-full sm:w-auto pl-3 pr-10 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors cursor-pointer">
                                <option value="">All Roles</option>
                                <option value="admin" {{ ($role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="staff" {{ ($role ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="adviser" {{ ($role ?? '') === 'adviser' ? 'selected' : '' }}>Adviser</option>
                                <option value="priest" {{ ($role ?? '') === 'priest' ? 'selected' : '' }}>Priest</option>
                                <option value="requestor" {{ ($role ?? '') === 'requestor' ? 'selected' : '' }}>Requestor</option>
                            </select>

                            @if(($search ?? false) || ($role ?? false))
                                <a href="{{ route('admin.users.index') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    <a href="{{ route('admin.users.archives') }}"
                       class="inline-flex items-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg shadow-md transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                        Archives
                    </a>
                    <a href="{{ route('admin.users.create') }}"
                       class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-md transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New User
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            @foreach(['user-created' => 'green', 'user-updated' => 'blue', 'user-deleted' => 'orange', 'user-archived' => 'orange'] as $key => $color)
                @if(session('status') === $key)
                    <div class="mb-6 p-4 bg-{{ $color }}-50 dark:bg-{{ $color }}-900/30 border-l-4 border-{{ $color }}-500 rounded-r-lg shadow-sm flex items-start gap-3">
                        <div class="flex-shrink-0 text-{{ $color }}-500">
                            @if($key === 'user-deleted' || $key === 'user-archived')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-{{ $color }}-800 dark:text-{{ $color }}-200">
                                {{ $key === 'user-created' ? 'Account Created Successfully' : ($key === 'user-updated' ? 'Account Updated Successfully' : 'Account Archived Successfully') }}
                            </p>
                            <p class="text-sm text-{{ $color }}-600 dark:text-{{ $color }}-300 mt-0.5">
                                {{ session('success') ?? ($key === 'user-archived' ? 'The user account has been moved to archives.' : 'The operation was completed successfully.') }}
                            </p>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Users Card - Desktop Table View -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">User Profile</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Role</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 font-bold tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($users as $user)
                                <tr data-user-id="{{ $user->id }}" class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-lg">
                                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                            {{ $user->role === 'staff' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                            {{ $user->role === 'adviser' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300' : '' }}
                                            {{ $user->role === 'priest' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                            {{ $user->role === 'requestor' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                        ">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                            {{ $user->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                            {{ $user->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                            {{ $user->status === 'suspended' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                        ">
                                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full
                                                {{ $user->status === 'active' ? 'bg-green-500' : '' }}
                                                {{ $user->status === 'pending' ? 'bg-yellow-500' : '' }}
                                                {{ $user->status === 'suspended' ? 'bg-red-500' : '' }}
                                            "></span>
                                            {{ $user->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2" x-data="{ showConfirm: false, isArchiving: false }">

                                            <!-- Edit Button -->
                                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                <span class="hidden sm:inline">Edit</span>
                                            </a>

                                            <!-- Archive Button -->
                                            <button @click="showConfirm = true"
                                                    :disabled="isArchiving"
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-red-600 dark:text-red-400 shadow-sm hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span class="hidden sm:inline">Archive</span>
                                            </button>

                                            <!-- Modal -->
                                            <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-title" style="display: none;">
                                                <div class="flex items-center justify-center min-h-screen px-3 py-6 sm:px-4 sm:py-8 lg:py-20">
                                                    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity duration-300" @click="showConfirm = false" aria-hidden="true"></div>
                                                    <div class="relative inline-block w-full max-w-sm sm:max-w-md lg:max-w-2xl bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all border border-gray-200 dark:border-gray-700">
                                                        <!-- Header -->
                                                        <div class="px-4 pt-5 pb-4 sm:px-6 sm:pt-6 sm:pb-5 lg:px-8 lg:pt-8 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-br from-gray-50 to-white dark:from-gray-750 dark:to-gray-800">
                                                            <div class="flex items-start gap-4 lg:gap-5">
                                                                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 sm:h-14 sm:w-14 lg:h-16 lg:w-16 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 shadow-lg">
                                                                    <svg class="h-6 w-6 sm:h-7 sm:w-7 lg:h-8 lg:w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <h2 id="modal-title" class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                                                                        Archive User Account
                                                                    </h2>
                                                                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-1">This action cannot be undone immediately</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Content Body -->
                                                        <div class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6 space-y-5">
                                                            <!-- User Name Highlight -->
                                                            <div class="p-3 sm:p-4 lg:p-5 bg-gradient-to-br from-blue-50 to-blue-50/50 dark:from-blue-900/30 dark:to-blue-900/10 border-l-4 border-blue-500 dark:border-blue-400 rounded-lg">
                                                                <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300">
                                                                    <span class="font-semibold text-gray-900 dark:text-gray-100">User:</span> {{ $user->full_name }}
                                                                </p>
                                                                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-2">
                                                                    <span class="font-medium text-gray-700 dark:text-gray-300">Email:</span> {{ $user->email }}
                                                                </p>
                                                            </div>

                                                            <!-- Warning Box -->
                                                            <div class="p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-orange-50 to-orange-50/50 dark:from-orange-900/25 dark:to-orange-900/5 border-l-4 border-orange-500 dark:border-orange-400 rounded-lg shadow-sm">
                                                                <div class="flex items-start gap-3 sm:gap-4">
                                                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-orange-600 dark:text-orange-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M5 17.5H3m6.5 0h10M3.75 13h16.5m-16.5-5h16.5M3.75 7.5H20.25" />
                                                                    </svg>
                                                                    <div class="flex-1 min-w-0">
                                                                        <p class="text-sm sm:text-base font-semibold text-orange-900 dark:text-orange-200 mb-3">What happens when you archive?</p>
                                                                        <ul class="text-sm sm:text-base space-y-2 text-orange-800 dark:text-orange-300">
                                                                            <li class="flex items-center gap-2">
                                                                                <span class="inline-block w-2 h-2 bg-orange-600 dark:bg-orange-400 rounded-full flex-shrink-0"></span>
                                                                                <span>User cannot log in</span>
                                                                            </li>
                                                                            <li class="flex items-center gap-2">
                                                                                <span class="inline-block w-2 h-2 bg-orange-600 dark:bg-orange-400 rounded-full flex-shrink-0"></span>
                                                                                <span>Account can be restored later</span>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Confirmation Question -->
                                                            <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300 font-semibold pt-2">
                                                                Are you sure you want to proceed?
                                                            </p>
                                                        </div>

                                                        <!-- Footer with Buttons -->
                                                        <div class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row gap-3 sm:gap-4">
                                                            <button type="button" @click="showConfirm = false" class="flex-1 order-2 sm:order-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm sm:text-base font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-0 transition-all duration-200">
                                                                Cancel
                                                            </button>
                                                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="flex-1 order-1 sm:order-2" x-on:submit="isArchiving = true">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" :disabled="isArchiving" :class="{ 'cursor-not-allowed': isArchiving }" class="w-full flex items-center justify-center gap-2 px-3 sm:px-4 py-2.5 sm:py-3 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 disabled:from-orange-400 disabled:to-orange-500 disabled:opacity-70 text-white text-xs sm:text-sm lg:text-base font-semibold rounded-lg shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600 dark:focus:ring-offset-0 transition-all duration-200 whitespace-nowrap">
                                                                    <template x-if="isArchiving">
                                                                        <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                        </svg>
                                                                    </template>
                                                                    <template x-if="!isArchiving">
                                                                        <svg class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                    </template>
                                                                    <span x-text="isArchiving ? 'Archiving...' : 'Yes, Archive User'"></span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                </svg>
                                            </div>
                                            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No users found</p>
                                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Try adjusting your search or filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden">
                    @forelse($users as $user)
                        <div data-user-id="{{ $user->id }}" class="p-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0" x-data="{ showConfirm: false, isArchiving: false }">
                            <!-- User Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-lg flex-shrink-0">
                                        {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $user->full_name }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize flex-shrink-0
                                    {{ $user->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                    {{ $user->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                    {{ $user->status === 'suspended' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                ">
                                    <span class="w-1.5 h-1.5 mr-1 rounded-full
                                        {{ $user->status === 'active' ? 'bg-green-500' : '' }}
                                        {{ $user->status === 'pending' ? 'bg-yellow-500' : '' }}
                                        {{ $user->status === 'suspended' ? 'bg-red-500' : '' }}
                                    "></span>
                                    {{ $user->status }}
                                </span>
                            </div>

                            <!-- Role Badge -->
                            <div class="mb-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium capitalize
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                    {{ $user->role === 'staff' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                    {{ $user->role === 'adviser' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300' : '' }}
                                    {{ $user->role === 'priest' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : '' }}
                                    {{ $user->role === 'requestor' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                ">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $user->role }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="w-full inline-flex items-center justify-center px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    <svg class="w-4 h-4 mr-1.5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    Edit
                                </a>
                                <button @click="showConfirm = true"
                                        :disabled="isArchiving"
                                        class="w-full inline-flex items-center justify-center px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Archive
                                </button>
                            </div>

                            <!-- Mobile Modal -->
                            <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" style="display: none;">
                                <div class="flex items-center justify-center min-h-screen px-3 py-6 sm:px-4 sm:py-8 lg:py-20">
                                    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity duration-300" @click="showConfirm = false" aria-hidden="true"></div>
                                    <div class="relative inline-block w-full max-w-sm sm:max-w-md lg:max-w-2xl bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all border border-gray-200 dark:border-gray-700">
                                        <!-- Header -->
                                        <div class="px-4 pt-5 pb-4 sm:px-6 sm:pt-6 sm:pb-5 lg:px-8 lg:pt-8 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-br from-gray-50 to-white dark:from-gray-750 dark:to-gray-800">
                                            <div class="flex items-start gap-4 lg:gap-5">
                                                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 sm:h-14 sm:w-14 lg:h-16 lg:w-16 rounded-full bg-gradient-to-br from-orange-500 to-orange-600 shadow-lg">
                                                    <svg class="h-6 w-6 sm:h-7 sm:w-7 lg:h-8 lg:w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h2 id="modal-title" class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                                                        Archive User Account
                                                    </h2>
                                                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-1">This action cannot be undone immediately</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Content Body -->
                                        <div class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6 space-y-5">
                                            <!-- User Name Highlight -->
                                            <div class="p-3 sm:p-4 lg:p-5 bg-gradient-to-br from-blue-50 to-blue-50/50 dark:from-blue-900/30 dark:to-blue-900/10 border-l-4 border-blue-500 dark:border-blue-400 rounded-lg">
                                                <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300">
                                                    <span class="font-semibold text-gray-900 dark:text-gray-100">User:</span> {{ $user->full_name }}
                                                </p>
                                                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-2">
                                                    <span class="font-medium text-gray-700 dark:text-gray-300">Email:</span> {{ $user->email }}
                                                </p>
                                            </div>

                                            <!-- Warning Box -->
                                            <div class="p-4 sm:p-5 lg:p-6 bg-gradient-to-br from-orange-50 to-orange-50/50 dark:from-orange-900/25 dark:to-orange-900/5 border-l-4 border-orange-500 dark:border-orange-400 rounded-lg shadow-sm">
                                                <div class="flex items-start gap-3 sm:gap-4">
                                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-orange-600 dark:text-orange-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M5 17.5H3m6.5 0h10M3.75 13h16.5m-16.5-5h16.5M3.75 7.5H20.25" />
                                                    </svg>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm sm:text-base font-semibold text-orange-900 dark:text-orange-200 mb-3">What happens when you archive?</p>
                                                        <ul class="text-sm sm:text-base space-y-2 text-orange-800 dark:text-orange-300">
                                                            <li class="flex items-center gap-2">
                                                                <span class="inline-block w-2 h-2 bg-orange-600 dark:bg-orange-400 rounded-full flex-shrink-0"></span>
                                                                <span>User cannot log in</span>
                                                            </li>
                                                            <li class="flex items-center gap-2">
                                                                <span class="inline-block w-2 h-2 bg-orange-600 dark:bg-orange-400 rounded-full flex-shrink-0"></span>
                                                                <span>Account can be restored later</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Confirmation Question -->
                                            <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300 font-semibold pt-2">
                                                Are you sure you want to proceed?
                                            </p>
                                        </div>

                                        <!-- Footer with Buttons -->
                                        <div class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row gap-3 sm:gap-4">
                                            <button type="button" @click="showConfirm = false" class="flex-1 order-2 sm:order-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm sm:text-base font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-0 transition-all duration-200">
                                                Cancel
                                            </button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="flex-1 order-1 sm:order-2" x-on:submit="isArchiving = true">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" :disabled="isArchiving" :class="{ 'cursor-not-allowed': isArchiving }" class="w-full flex items-center justify-center gap-2 px-3 sm:px-4 py-2.5 sm:py-3 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 disabled:from-orange-400 disabled:to-orange-500 disabled:opacity-70 text-white text-xs sm:text-sm lg:text-base font-semibold rounded-lg shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-600 dark:focus:ring-offset-0 transition-all duration-200 whitespace-nowrap">
                                                    <template x-if="isArchiving">
                                                        <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </template>
                                                    <template x-if="!isArchiving">
                                                        <svg class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </template>
                                                    <span x-text="isArchiving ? 'Archiving...' : 'Yes, Archive User'"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No users found</p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Try adjusting your search or filters.</p>
                        </div>
                    @endforelse
                </div>

                @if($users->hasPages())
                <div class="px-4 sm:px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.realtimeUpdates) {
        return;
    }

    const removeUserFromList = (userId) => {
        if (!userId) {
            return;
        }

        document.querySelectorAll(`[data-user-id="${userId}"]`).forEach((el) => {
            el.remove();
        });

        if (!document.querySelector('[data-user-id]')) {
            window.location.reload();
        }
    };

    window.realtimeUpdates.on('User:delete', (data) => {
        removeUserFromList(data?.id);
    });

    window.realtimeUpdates.on('User:update', (data) => {
        // Keep rendering logic simple and consistent by reloading after user updates.
        if (data?.id) {
            window.location.reload();
        }
    });

    window.realtimeUpdates.on('User:create', (data) => {
        if (data?.id) {
            window.location.reload();
        }
    });

    window.realtimeUpdates.on('User:restore', (data) => {
        if (data?.id) {
            window.location.reload();
        }
    });
});
</script>
