@extends('layouts.app')

@section('title', 'Booking Requests Summary')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Booking Requests Summary</h2>
                <div>
                    <a href="{{ route('adviser.organization-bookings.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> View All Requests
                    </a>
                    <a href="{{ route('adviser.organization-bookings.calendar') }}" class="btn btn-outline-info">
                        <i class="fas fa-calendar"></i> Calendar View
                    </a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">{{ $stats['total'] }}</h3>
                                    <p class="mb-0">Total Requests</p>
                                </div>
                                <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-warning text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">{{ $stats['pending'] }}</h3>
                                    <p class="mb-0">Pending Review</p>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">{{ $stats['approved'] }}</h3>
                                    <p class="mb-0">Approved</p>
                                </div>
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">{{ $stats['overdue'] }}</h3>
                                    <p class="mb-0">Overdue</p>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Requests -->
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Recent Requests Requiring Action
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($recentRequests->isEmpty())
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <p class="text-muted">No pending requests at this time.</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Organization</th>
                                                <th>Requestor</th>
                                                <th>Purpose</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentRequests as $request)
                                                <tr class="{{ $request->isOverdue() ? 'table-warning' : '' }}">
                                                    <td>
                                                        <strong>{{ $request->organization->name }}</strong>
                                                    </td>
                                                    <td>
                                                        {{ $request->requestor->name }}
                                                    </td>
                                                    <td>
                                                        <span title="{{ $request->purpose }}">
                                                            {{ Str::limit($request->purpose, 40) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($request->isOverdue())
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-exclamation-triangle"></i> Overdue
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-clock"></i> Pending
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $request->created_at->format('M j') }}
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $request->created_at->diffForHumans() }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('adviser.organization-bookings.show', $request) }}" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i> Review
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Organizations Overview -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-building"></i> Organizations Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($organizationStats->isEmpty())
                                <p class="text-muted">No organization data available.</p>
                            @else
                                @foreach($organizationStats as $org)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>{{ $org->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $org->requests_count }} requests</small>
                                        </div>
                                        <div class="text-end">
                                            @if($org->pending_requests_count > 0)
                                                <span class="badge bg-warning">
                                                    {{ $org->pending_requests_count }} pending
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark">
                                                    All reviewed
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie"></i> Quick Stats
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <h4 class="text-success">{{ number_format($stats['approval_rate'], 1) }}%</h4>
                                    <small class="text-muted">Approval Rate</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <h4 class="text-primary">{{ $stats['avg_response_time'] }}</h4>
                                    <small class="text-muted">Avg Response</small>
                                </div>
                                <div class="col-12">
                                    <h4 class="text-info">{{ $stats['this_month'] }}</h4>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-warning {
    --bs-table-bg: #fff3cd;
    --bs-table-border-color: #ffecb5;
}

.card-body .badge {
    font-size: 0.75rem;
}

.opacity-75 {
    opacity: 0.75;
}
</style>
@endpush