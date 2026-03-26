@extends('layouts.app')

@section('title', 'Organization Booking Requests')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Organization Booking Requests</h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">Review and manage booking requests from your organizations</p>
                </div>
                <a href="{{ route('adviser.dashboard') }}" class="btn-secondary inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('adviser.organization-bookings.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    All Requests
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ !request('status') ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600' }}">{{ $counts['all'] ?? 0 }}</span>
                </a>
                <a href="{{ route('adviser.organization-bookings.index', ['status' => 'pending']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request('status') === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Pending
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ request('status') === 'pending' ? 'bg-yellow-400' : 'bg-yellow-200 text-yellow-800' }}">{{ $counts['pending'] ?? 0 }}</span>
                </a>
                <a href="{{ route('adviser.organization-bookings.index', ['status' => 'approved']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request('status') === 'approved' ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Approved
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ request('status') === 'approved' ? 'bg-green-500' : 'bg-green-200 text-green-800' }}">{{ $counts['approved'] ?? 0 }}</span>
                </a>
                <a href="{{ route('adviser.organization-bookings.index', ['status' => 'rejected']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request('status') === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    Rejected
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ request('status') === 'rejected' ? 'bg-red-500' : 'bg-red-200 text-red-800' }}">{{ $counts['rejected'] ?? 0 }}</span>
                </a>
            </div>
        </div>

        <!-- Status Messages -->
        @if (session('status'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-800 dark:text-green-200 font-medium">
                        {{ session('message', 'Action completed successfully.') }}
                    </span>
                </div>
            </div>
        @endif

        <!-- Requests List -->
        @if($requests->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No booking requests found</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    @if(request('status'))
                        No {{ request('status') }} requests at this time.
                    @else
                        There are no organization booking requests to review.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($requests as $request)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                        <!-- Desktop Layout -->
                        <div class="hidden sm:block p-6">
                            <div class="flex justify-between items-start">
                                <!-- Left Side: Icon and Content -->
                                <div class="flex items-start gap-4 flex-1">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center
                                            {{ $request->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }}
                                            {{ $request->status === 'approved' ? 'bg-green-100 dark:bg-green-900/30' : '' }}
                                            {{ $request->status === 'rejected' ? 'bg-red-100 dark:bg-red-900/30' : '' }}">
                                            @if($request->status === 'pending')
                                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @elseif($request->status === 'approved')
                                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $request->activity_name }}
                                        </h3>
                                        <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">
                                            {{ $request->organization->org_name }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                            {{ Str::limit($request->purpose, 100) }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mt-3">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                {{ $request->requestor->full_name ?? $request->requestor->name }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $request->requested_date->format('M d, Y \a\t g:i A') }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Submitted {{ $request->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Side: Status Badge and Action Button -->
                                <div class="flex flex-col items-end gap-3 ml-4">
                                    <span class="inline-flex px-3 py-1.5 text-sm font-semibold rounded-full whitespace-nowrap
                                        {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : '' }}
                                        {{ $request->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : '' }}
                                        {{ $request->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : '' }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                    @if($request->status === 'pending')
                                        <a href="{{ route('adviser.organization-bookings.show', $request) }}"
                                           class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm whitespace-nowrap">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                                            </svg>
                                            Review Request
                                        </a>
                                    @else
                                        <a href="{{ route('adviser.organization-bookings.show', $request) }}"
                                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm whitespace-nowrap">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View Details
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Layout -->
                        <div class="block sm:hidden p-4">
                            <div class="space-y-4">
                                <!-- Header with Icon and Status -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3 flex-1 min-w-0">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                                {{ $request->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }}
                                                {{ $request->status === 'approved' ? 'bg-green-100 dark:bg-green-900/30' : '' }}
                                                {{ $request->status === 'rejected' ? 'bg-red-100 dark:bg-red-900/30' : '' }}">
                                                @if($request->status === 'pending')
                                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @elseif($request->status === 'approved')
                                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                {{ $request->activity_name }}
                                            </h3>
                                            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium truncate">
                                                {{ $request->organization->org_name }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full whitespace-nowrap flex-shrink-0
                                        {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : '' }}
                                        {{ $request->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : '' }}
                                        {{ $request->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : '' }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </div>

                                <!-- Purpose -->
                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Purpose</p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
                                        {{ $request->purpose }}
                                    </p>
                                </div>

                                <!-- Info Grid - Requestor, Date, Submitted -->
                                <div class="grid grid-cols-1 gap-3 text-xs">
                                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span class="truncate">{{ $request->requestor->full_name ?? $request->requestor->name }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="truncate">{{ $request->requested_date->format('M d, Y g:i A') }}</span>
                                    </div>
                                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>Submitted {{ $request->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                    @if($request->status === 'pending')
                                        <a href="{{ route('adviser.organization-bookings.show', $request) }}"
                                           class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                                            </svg>
                                            Review Request
                                        </a>
                                    @else
                                        <a href="{{ route('adviser.organization-bookings.show', $request) }}"
                                           class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View Details
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="mt-6">
                    {{ $requests->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
