@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-heading text-3xl font-bold text-gray-900 dark:text-white mb-2">Unnoticed Reservations</h1>
        <p class="text-muted dark:text-gray-400">Reservations awaiting adviser response for over 24 hours</p>
    </div>

    <!-- Flash Messages -->
    @if(session('status'))
        <div class="mb-6">
            <span class="badge-success">
                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('status') }}
            </span>
        </div>
    @endif

    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Reservations Card -->
    <div class="card">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            @if($reservations->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-muted dark:text-gray-400 text-lg">No unnoticed reservations found</p>
            </div>
            @else
            <div class="table-responsive">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" role="table" aria-label="Unnoticed reservations">
                <caption class="sr-only">List of unnoticed reservation requests awaiting adviser response</caption>
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requestor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Organization</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Service</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Schedule</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time Waiting</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($reservations as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            #{{ $r->reservation_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $r->user?->full_name ?? $r->user?->email ?? 'Unknown User' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-300">{{ $r->organization?->org_name ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-300">{{ $r->service->service_name ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ optional($r->schedule_date)->format('M d, Y') }}</div>
                            <div class="text-xs text-muted dark:text-gray-400">{{ optional($r->schedule_date)->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $r->created_at->diffForHumans() }}</div>
                            <div class="text-xs text-muted dark:text-gray-400">Since {{ $r->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('staff.reservations.show', $r->reservation_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors duration-150">
                                View Details →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>

        <!-- Mobile & Tablet Card View -->
        <div class="lg:hidden">
            @if($reservations->isEmpty())
                <div class="p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-muted dark:text-gray-400 text-lg">No unnoticed reservations found</p>
                </div>
            @else
                <div class="space-y-3 p-4">
                    @foreach($reservations as $r)
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-red-200 dark:border-red-900/50 shadow-sm hover:shadow-md transition-all duration-150 p-4">
                            <!-- Header: ID, Warning Badge -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">#{{ $r->reservation_id }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5 animate-pulse"></span>
                                        Awaiting Response
                                    </span>
                                </div>
                            </div>

                            <!-- Requestor Name -->
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 truncate">
                                {{ $r->user?->full_name ?? $r->user?->email ?? 'Unknown User' }}
                            </h3>

                            <!-- Info Grid -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Service</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $r->service->service_name ?? '—' }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Schedule</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ optional($r->schedule_date)->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($r->schedule_date)->format('h:i A') }}</p>
                                </div>
                                <div class="col-span-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Organization</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $r->organization?->org_name ?? '—' }}</p>
                                </div>
                                <div class="col-span-2 bg-red-50 dark:bg-red-900/10 rounded-lg p-3 border border-red-100 dark:border-red-900/30">
                                    <p class="text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wide mb-1">Waiting Since</p>
                                    <p class="text-sm font-medium text-red-900 dark:text-red-200">{{ $r->created_at->diffForHumans() }}</p>
                                    <p class="text-xs text-red-700 dark:text-red-300">{{ $r->created_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="{{ route('staff.reservations.show', $r->reservation_id) }}"
                               class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Follow Up Now
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        {{ $reservations->links() }}
    </div>
</div>
@endsection
