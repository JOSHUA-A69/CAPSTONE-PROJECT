@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-heading text-3xl font-bold text-gray-900 dark:text-white mb-2">Manage Reservations</h1>
        <p class="text-muted dark:text-gray-400">Review and manage reservation requests</p>
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

    <!-- Search / Filter -->
    <form method="GET" class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label dark:text-gray-300">Search</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-input dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" placeholder="Requestor, organization...">
                </div>
                <div>
                    <label class="form-label dark:text-gray-300">Status</label>
                    <select name="status" class="form-input dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="adviser_approved" {{ request('status') === 'adviser_approved' ? 'selected' : '' }}>Adviser Approved</option>
                        <option value="pending_priest_reassignment" {{ request('status') === 'pending_priest_reassignment' ? 'selected' : '' }}>Priest Declined (Reassign)</option>
                        <option value="admin_approved" {{ request('status') === 'admin_approved' ? 'selected' : '' }}>Admin Approved</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('staff.reservations.index') }}" class="btn-ghost">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <!-- Reservations Card -->
    <div class="card">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            @if($reservations->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-muted dark:text-gray-400 text-lg">No reservations found</p>
            </div>
            @else
            <div class="table-responsive">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" role="table" aria-label="All reservations">
                <caption class="sr-only">List of all spiritual activity reservations for staff review</caption>
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requestor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Organization</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Service</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Schedule</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
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
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $r->user->full_name ?? $r->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-300">{{ $r->organization->org_name ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-300">{{ $r->service->service_name ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ optional($r->schedule_date)->format('M d, Y') }}</div>
                            <div class="text-xs text-muted dark:text-gray-400">{{ optional($r->schedule_date)->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($r->status === 'pending')
                                <span class="badge-warning">Awaiting Adviser</span>
                            @elseif($r->status === 'adviser_approved')
                                <span class="badge-warning">Awaiting Priest</span>
                            @elseif($r->status === 'admin_approved')
                                <span class="badge-info">Awaiting Admin</span>
                            @elseif($r->status === 'approved')
                                <span class="badge-success">Approved by Admin</span>
                            @elseif($r->status === 'confirmed')
                                <span class="badge-success">Confirmed</span>
                            @elseif($r->status === 'completed')
                                <span class="badge-success">Completed</span>
                            @elseif($r->status === 'rejected')
                                <span class="badge-danger">Rejected</span>
                            @elseif($r->status === 'cancelled')
                                <span class="badge-secondary">Cancelled</span>
                            @else
                                <span class="badge-secondary">{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span>
                            @endif
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
                    <p class="text-muted dark:text-gray-400 text-lg">No reservations found</p>
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($reservations as $r)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">#{{ $r->reservation_id }}</span>
                                        @if($r->status === 'pending')
                                            <span class="badge-warning text-xs">Awaiting Adviser</span>
                                        @elseif($r->status === 'adviser_approved')
                                            <span class="badge-warning text-xs">Awaiting Priest</span>
                                        @elseif($r->status === 'admin_approved')
                                            <span class="badge-info text-xs">Awaiting Admin</span>
                                        @elseif($r->status === 'approved')
                                            <span class="badge-success text-xs">Approved</span>
                                        @elseif($r->status === 'confirmed')
                                            <span class="badge-success text-xs">Confirmed</span>
                                        @elseif($r->status === 'completed')
                                            <span class="badge-success text-xs">Completed</span>
                                        @elseif($r->status === 'rejected')
                                            <span class="badge-danger text-xs">Rejected</span>
                                        @elseif($r->status === 'cancelled')
                                            <span class="badge-secondary text-xs">Cancelled</span>
                                        @else
                                            <span class="badge-secondary text-xs">{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span>
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $r->user->full_name ?? $r->user->email }}
                                    </h3>
                                </div>
                            </div>

                            <!-- Card Body - Grid Layout -->
                            <div class="grid grid-cols-2 gap-2 mb-4 text-sm">
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2.5">
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Service</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $r->service->service_name ?? '—' }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2.5">
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Schedule</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ optional($r->schedule_date)->format('M d, Y') }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ optional($r->schedule_date)->format('h:i A') }}</span>
                                </div>
                                <div class="col-span-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2.5">
                                    <span class="block text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Organization</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $r->organization->org_name ?? '—' }}</span>
                                </div>
                            </div>

                            <!-- Card Action -->
                            <a href="{{ route('staff.reservations.show', $r->reservation_id) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Details
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
