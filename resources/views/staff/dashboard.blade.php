<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">CREaM Staff Dashboard</h2>

            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                    @endphp

                    Welcome, {{ $displayName }}! This is your staff dashboard.

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="{{ route('staff.reservations.index') }}" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="font-semibold">Manage Reservations</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Review, approve, and triage reservation requests.</div>
                        </a>
                        <a href="{{ route('staff.organizations.index') }}" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="font-semibold">Manage Organizations</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Create and edit organizations and assign advisers.</div>
                        </a>
                        {{-- Services management route not yet implemented
                        <a href="{{ route('staff.services.index') }}" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="font-semibold">Manage Services</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Maintain the liturgical/ministry services catalog.</div>
                        </a>
                        --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
