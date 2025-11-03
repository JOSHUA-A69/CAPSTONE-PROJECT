@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Manage Venues</h1>
    @if(session('success'))
        <div class="mb-4 text-green-700 dark:text-green-200 bg-green-100 dark:bg-green-900/30 p-3 rounded-lg border border-green-200 dark:border-green-800">{{ session('success') }}</div>
    @endif
    <a href="{{ route('admin.venues.create') }}" class="btn-primary mb-6 inline-block">Add New Venue</a>
    
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="table-auto w-full">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700">Venue Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700">Capacity</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700">Location</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($venues as $venue)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ $venue->name }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ $venue->capacity ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ $venue->location ?? '—' }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.venues.edit', $venue->venue_id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">Edit</a>
                        <form action="{{ route('admin.venues.destroy', $venue->venue_id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition" onclick="return confirm('Delete this venue?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
