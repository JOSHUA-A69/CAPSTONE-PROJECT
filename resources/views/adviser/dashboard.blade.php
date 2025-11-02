<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Organization Adviser Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @php
                        $user = auth()->user();
                        $displayName = $user->first_name ?? $user->name ?? $user->email ?? 'User';
                    @endphp

                    <p class="text-lg mb-6">Welcome, {{ $displayName }}! This is your adviser dashboard.</p>

                    <div class="mb-6">
                        <a href="{{ route('adviser.reservations.index') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            Review Reservation Requests
                        </a>
                    </div>

                    <div class="mt-6">
                        <div class="p-6 bg-white dark:bg-gray-700 rounded-lg shadow-md border border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assigned Organization</h3>
                            @if($user->organizations->isEmpty())
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">You are not assigned to any organization yet.</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Staff can assign you to an organization via the Organizations management page.</p>
                            @else
                                <ul class="mt-3 space-y-3">
                                    @foreach($user->organizations as $org)
                                        <li class="pb-3 border-b border-gray-200 dark:border-gray-600 last:border-0 last:pb-0">
                                            <div class="font-semibold text-gray-900 dark:text-white mb-1">{{ $org->org_name }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $org->org_desc ?? 'Serves at the altar and proclaims the Scriptures.' }}</div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
