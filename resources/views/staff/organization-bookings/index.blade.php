@extends('layouts.app')

@section('title', 'Organization Booking Monitoring')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Organization Booking Monitoring</h2>
                <div>
                    <a href="{{ route('staff.organization-bookings.overdue') }}" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Overdue Requests ({{ $overdueCount ?? 0 }})
                    </a>
                </div>
            </div>

            <!-- Alert Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">{{ $stats['total'] ?? 0 }}</h3>
                                    <p class="mb-0">Total Requests</p>
                                </div>
                                <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">{{ $stats['pending'] ?? 0 }}</h3>
                                    <p class="mb-0">Pending Review</p>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">{{ $stats['overdue'] ?? 0 }}</h3>
                                    <p class="mb-0">Overdue</p>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">{{ $stats['this_week'] ?? 0 }}</h3>
                                    <p class="mb-0">This Week</p>
                                </div>
                                <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Options -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Organization</label>
                            <select name="organization_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Organizations</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" 
                                            {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                        {{ $org->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select name="date_range" class="form-select" onchange="this.form.submit()">
                                <option value="">All Time</option>
                                <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <a href="{{ route('staff.organization-bookings.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                                <button type="button" class="btn btn-primary" onclick="exportData()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table"></i> Booking Requests
                        @if(request()->hasAny(['status', 'organization_id', 'date_range']))
                            <small class="text-muted">(Filtered)</small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($requests->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No booking requests found with the current filters.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Organization</th>
                                        <th>Requestor</th>
                                        <th>Adviser</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Date Submitted</th>
                                        <th>Response Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr class="{{ $request->isOverdue() ? 'table-warning' : '' }}">
                                            <td>
                                                <strong>#{{ $request->id }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $request->organization->name }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $request->requestor->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $request->requestor->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $request->organization->adviser->name ?? 'N/A' }}</strong>
                                                    @if($request->organization->adviser)
                                                        <br>
                                                        <small class="text-muted">{{ $request->organization->adviser->email }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-wrap" style="max-width: 200px;">
                                                    {{ Str::limit($request->purpose, 50) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($request->status === 'pending')
                                                    @if($request->isOverdue())
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-exclamation-triangle"></i> Overdue
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </span>
                                                    @endif
                                                @elseif($request->status === 'approved')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Approved
                                                    </span>
                                                @elseif($request->status === 'rejected')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times"></i> Rejected
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $request->created_at->format('M d, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $request->created_at->format('g:i A') }}</small>
                                            </td>
                                            <td>
                                                @if($request->status !== 'pending')
                                                    @php
                                                        $responseTime = $request->approved_at ?? $request->rejected_at;
                                                        $hours = $request->created_at->diffInHours($responseTime);
                                                    @endphp
                                                    @if($hours < 24)
                                                        <span class="text-success">{{ $hours }}h</span>
                                                    @elseif($hours < 72)
                                                        <span class="text-warning">{{ number_format($hours / 24, 1) }}d</span>
                                                    @else
                                                        <span class="text-danger">{{ number_format($hours / 24, 1) }}d</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">
                                                        {{ $request->created_at->diffForHumans() }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('staff.organization-bookings.show', $request) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($request->status === 'pending' && $request->isOverdue())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-warning"
                                                                onclick="sendReminder({{ $request->id }})"
                                                                title="Send Reminder">
                                                            <i class="fas fa-bell"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} 
                                of {{ $requests->total() }} results
                            </div>
                            {{ $requests->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reminder Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reminderForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Send a reminder email to the adviser about this overdue request?</p>
                    <div class="mb-3">
                        <label class="form-label">Additional Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" 
                                  placeholder="Add any additional context or urgency notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-bell"></i> Send Reminder
                    </button>
                </div>
            </form>
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

.opacity-75 {
    opacity: 0.75;
}
</style>
@endpush

@push('scripts')
<script>
function sendReminder(requestId) {
    const form = document.getElementById('reminderForm');
    form.action = `/staff/organization-bookings/${requestId}/send-reminder`;
    new bootstrap.Modal(document.getElementById('reminderModal')).show();
}

function exportData() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open(`${window.location.pathname}?${params.toString()}`);
}

// Auto-refresh for real-time monitoring
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 300000); // Refresh every 5 minutes
</script>
@endpush