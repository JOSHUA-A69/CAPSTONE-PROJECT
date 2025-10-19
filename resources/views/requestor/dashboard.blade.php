<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Requestor Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                    @endphp

                    Welcome, {{ $displayName }}! This is your requestor dashboard.

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="{{ route('requestor.reservations.index') }}" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="font-semibold">My Reservations</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">View the status of your reservation requests.</div>
                        </a>
                        <a href="{{ route('requestor.reservations.create') }}" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="font-semibold">New Reservation</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Submit a new request for a service and venue.</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
