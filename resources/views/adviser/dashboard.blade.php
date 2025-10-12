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

                    Welcome, {{ $displayName }}! This is your adviser dashboard.

                    <div class="mt-4">
                        <div class="p-4 bg-white rounded shadow">
                            <h3 class="text-lg font-medium">Assigned Organization</h3>
                            @if($user->organizations->isEmpty())
                                <p class="text-sm text-gray-600 mt-2">You are not assigned to any organization yet.</p>
                                <p class="text-sm text-gray-500 mt-2">Staff can assign you to an organization via the Organizations management page.</p>
                            @else
                                <ul class="mt-2 space-y-2">
                                    @foreach($user->organizations as $org)
                                        <li>
                                            <div class="font-medium">{{ $org->org_name }}</div>
                                            <div class="text-sm text-gray-600">{{ Str::limit($org->org_desc ?? '—', 120) }}</div>
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
