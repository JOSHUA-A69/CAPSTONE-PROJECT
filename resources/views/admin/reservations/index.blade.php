@extends('layouts.app')

@section('title', 'All Reservations')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header & Stats -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Reservations</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track all system reservations.</p>
            </div>
            @isset($pendingCount)
                <div class="flex-shrink-0 inline-flex items-center px-4 py-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg text-yellow-700 dark:text-yellow-300 shadow-sm">
                    <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">{{ $pendingCount }} Pending Review</span>
                </div>
            @endisset
        </div>

        <!-- Filter Bar -->
        <div class="mb-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="q" value="{{ $search ?? '' }}" 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-700 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm shadow-sm transition-colors" 
                        placeholder="Search...">
                </div>

                <div class="w-full sm:w-auto">
                    <select name="status" class="block w-full py-2.5 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors">
                        <option value="">All Statuses</option>
                        @foreach(($statuses ?? []) as $s)
                            <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-md transition-all duration-200 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex-shrink-0">
                    Filter
                </button>
            </form>
        </div>

        <!-- Reservations Table -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700">
            <div class="overflow-x-auto">
                @if(($reservations ?? null) && $reservations->count())
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Requestor</th>
                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Purpose</th>
                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Schedule</th>
                            <th scope="col" class="px-6 py-4 font-bold tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 font-bold tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($reservations as $r)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 font-mono text-xs">
                                #{{ $r->reservation_id }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ optional($r->user)->full_name ?? 'Guest User' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ optional($r->user)->email ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                {{ $r->purpose }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                @if($r->schedule_date)
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $r->schedule_date->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-500">{{ $r->schedule_date->format('g:i A') }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Not set</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusLabel = match($r->status) {
                                        'approved' => 'Approved',
                                        'admin_approved' => 'Wait Admin',
                                        'adviser_approved' => 'Wait Priest',
                                        'pending' => 'Pending',
                                        'confirmed' => 'Confirmed',
                                        'completed' => 'Completed',
                                        'rejected' => 'Rejected',
                                        'cancelled' => 'Cancelled',
                                        default => ucfirst(str_replace('_', ' ', $r->status))
                                    };
                                    
                                    $statusClasses = match($r->status) {
                                        'approved', 'confirmed', 'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border border-green-200 dark:border-green-800',
                                        'rejected', 'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800',
                                        'admin_approved', 'adviser_approved' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
                                        default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.reservations.show', $r->reservation_id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No reservations found</h3>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">Try adjusting your search or filter options.</p>
                </div>
                @endif
            </div>
            
            @if(($reservations ?? null) && $reservations->hasPages())
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                {{ $reservations->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
