<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manage Users</h2>
    </x-slot>

    <style>
        @keyframes pulse-once {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.9; transform: scale(1.02); }
        }
        .animate-pulse-once {
            animation: pulse-once 0.6s ease-in-out;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Search Bar and Filter -->
                    <div class="mb-6">
                        <form method="GET" action="{{ route('admin.users.index') }}" id="userFilterForm" class="flex flex-wrap gap-2 items-center">
                            <div class="relative flex-1 max-w-md">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       name="search" 
                                       value="{{ $search ?? '' }}" 
                                       placeholder="Search by name or email..." 
                                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <select name="role" 
                                    onchange="document.getElementById('userFilterForm').submit();"
                                    class="block px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">All Roles</option>
                                <option value="admin" {{ ($role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="staff" {{ ($role ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="adviser" {{ ($role ?? '') === 'adviser' ? 'selected' : '' }}>Adviser</option>
                                <option value="priest" {{ ($role ?? '') === 'priest' ? 'selected' : '' }}>Priest</option>
                                <option value="requestor" {{ ($role ?? '') === 'requestor' ? 'selected' : '' }}>Requestor</option>
                            </select>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Search
                            </button>
                            @if(($search ?? false) || ($role ?? false))
                                <a href="{{ route('admin.users.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Users</h3>
                        <div class="flex gap-3">
                            <a href="{{ route('admin.users.archives') }}" 
                               class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                                Archives
                            </a>
                            <a href="{{ route('admin.users.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Create User
                            </a>
                        </div>
                    </div>

                    @if(session('status') === 'user-created')
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-200 rounded-lg shadow-lg animate-pulse-once">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="font-bold text-lg">✓ Account Created Successfully!</p>
                                    @if(session('success'))
                                        <p class="mt-1 text-sm">{{ session('success') }}</p>
                                    @else
                                        <p class="mt-1 text-sm">The user account has been created and is now active.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('status') === 'user-updated')
                        <div class="mb-4 p-4 bg-blue-100 dark:bg-blue-900 border-l-4 border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-200 rounded-lg shadow-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="font-bold text-lg">✓ Account Updated Successfully!</p>
                                    <p class="mt-1 text-sm">The user account has been updated.</p>
                                </div>
                            </div>
                        </div>
                    @endif

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

                    @if(session('status') === 'user-deleted' || session('status') === 'user-archived')
                        <div class="mb-4 p-4 bg-orange-100 dark:bg-orange-900/30 border-l-4 border-orange-500 dark:border-orange-400 text-orange-700 dark:text-orange-200 rounded-lg shadow-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                                <div class="flex-1">
                                    <p class="font-bold text-lg">✓ Account Archived Successfully!</p>
                                    <p class="mt-1 text-sm">The user account has been archived. You can restore it from the Archives.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold">Error:</span> {{ session('error') }}
                        </div>
                    @endif

                    <table class="w-full table-auto">
                        <thead>
                            <tr class="text-left">
                                <th class="px-2 py-1">Name</th>
                                <th class="px-2 py-1">Email</th>
                                <th class="px-2 py-1">Role</th>
                                <th class="px-2 py-1">Status</th>
                                <th class="px-2 py-1">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-t">
                                    <td class="px-2 py-2">{{ $user->full_name }}</td>
                                    <td class="px-2 py-2">{{ $user->email }}</td>
                                    <td class="px-2 py-2">{{ $user->role }}</td>
                                    <td class="px-2 py-2">{{ $user->status }}</td>
                                    <td class="px-2 py-2">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-800 mr-3">Edit</a>

                                        <div x-data="{ showConfirm: false, isArchiving: false }" style="display:inline">
                                            <button @click="showConfirm = true"
                                                    type="button"
                                                    :disabled="isArchiving"
                                                    class="text-orange-600 hover:text-orange-800 font-medium">
                                                Archive
                                            </button>

                                            <!-- Confirmation Dialog -->
                                            <div x-show="showConfirm"
                                                 x-cloak
                                                 class="fixed inset-0 z-50 overflow-y-auto"
                                                 aria-labelledby="modal-title"
                                                 role="dialog"
                                                 aria-modal="true"
                                                 style="display: none;">
                                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                    <!-- Background overlay -->
                                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                                         @click="showConfirm = false"
                                                         aria-hidden="true"></div>

                                                    <!-- Modal panel -->
                                                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                            <div class="sm:flex sm:items-start">
                                                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 dark:bg-orange-900 sm:mx-0 sm:h-10 sm:w-10">
                                                                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                                    </svg>
                                                                </div>
                                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                                                        Archive User Account
                                                                    </h3>
                                                                    <div class="mt-2">
                                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                            Are you sure you want to archive <strong>{{ $user->full_name }}</strong>? You can restore this account later from the Archives.
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                                                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        @click="isArchiving = true"
                                                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:w-auto sm:text-sm transition duration-150">
                                                                    OK
                                                                </button>
                                                            </form>
                                                            <button type="button"
                                                                    @click="showConfirm = false"
                                                                    class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition duration-150">
                                                                Cancel
                                                            </button>
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

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
