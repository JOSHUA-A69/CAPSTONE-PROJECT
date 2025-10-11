<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">CREaM Staff Dashboard</h2>
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
                    <div class="mt-4">
                        <div class="p-4 bg-white rounded shadow">
                            <h3 class="text-lg font-medium">Manage</h3>
                            <ul class="mt-2 space-y-1">
                                <li><a href="{{ route('staff.organizations.index') }}" class="text-blue-600">Organizations</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
