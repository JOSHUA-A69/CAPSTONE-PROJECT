@extends('layouts.app')

@section('title', 'Organization Booking Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Organization Booking Management</h2>
                <div>
                    <a href="{{ route('admin.organization-bookings.reports') }}" class="btn btn-info me-2">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData()">
                                <i class="fas fa-download"></i> Export Data
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="bulkActions()">
                                <i class="fas fa-tasks"></i> Bulk Actions
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="systemSettings()">
                                <i class="fas fa-cog"></i> System Settings
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Dashboard Metrics -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-6">{{ $metrics['total'] ?? 0 }}</div>
                            <small>Total Requests</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-6">{{ $metrics['pending'] ?? 0 }}</div>
                            <small>Pending</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-6">{{ $metrics['overdue'] ?? 0 }}</div>
                            <small>Overdue</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-6">{{ $metrics['approved'] ?? 0 }}</div>
                            <small>Approved</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-6">{{ number_format($metrics['approval_rate'] ?? 0, 1) }}%</div>
                            <small>Approval Rate</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-secondary text-white shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-6">{{ $metrics['avg_response_time'] ?? 'N/A' }}</div>
                            <small>Avg Response</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-2">
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
                        <div class="col-md-2">
                            <label class="form-label">Adviser</label>
                            <select name="adviser_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Advisers</option>
                                @foreach($advisers as $adviser)
                                    <option value="{{ $adviser->id }}" 
                                            {{ request('adviser_id') == $adviser->id ? 'selected' : '' }}>
                                        {{ $adviser->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date Range</label>
                            <select name="date_range" class="form-select" onchange="this.form.submit()">
                                <option value="">All Time</option>
                                <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="quarter" {{ request('date_range') === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search requests..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex">
                                <a href="{{ route('admin.organization-bookings.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table"></i> Booking Requests
                            @if(request()->hasAny(['status', 'organization_id', 'adviser_id', 'date_range', 'search']))
                                <small class="text-muted">(Filtered)</small>
                            @endif
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" onclick="selectAll()">
                                <i class="fas fa-check-square"></i> Select All
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearSelection()">
                                <i class="fas fa-square"></i> Clear Selection
                            </button>
                        </div>
                    </div>
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
                                        <th width="50">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                        </th>
                                        <th>ID</th>
                                        <th>Organization</th>
                                        <th>Requestor</th>
                                        <th>Adviser</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>SLA Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr class="{{ $request->isOverdue() ? 'table-warning' : '' }}">
                                            <td>
                                                <input type="checkbox" class="form-check-input request-checkbox" 
                                                       value="{{ $request->id }}">
                                            </td>
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
                                                @if($request->organization->adviser)
                                                    <div>
                                                        <strong>{{ $request->organization->adviser->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $request->organization->adviser->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="badge bg-warning">No Adviser</span>
                                                    <br>
                                                    <button class="btn btn-xs btn-outline-primary mt-1" 
                                                            onclick="reassignAdviser({{ $request->id }})">
                                                        Assign
                                                    </button>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-wrap" style="max-width: 200px;" 
                                                      title="{{ $request->purpose }}">
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
                                                @php
                                                    $hoursSinceCreation = $request->created_at->diffInHours(now());
                                                    $slaThreshold = 72; // 72 hours SLA
                                                @endphp
                                                
                                                @if($request->status !== 'pending')
                                                    @php
                                                        $responseTime = $request->approved_at ?? $request->rejected_at;
                                                        $responseHours = $request->created_at->diffInHours($responseTime);
                                                    @endphp
                                                    @if($responseHours <= $slaThreshold)
                                                        <span class="badge bg-success">Within SLA</span>
                                                        <br>
                                                        <small class="text-muted">{{ number_format($responseHours, 1) }}h</small>
                                                    @else
                                                        <span class="badge bg-danger">SLA Breached</span>
                                                        <br>
                                                        <small class="text-muted">{{ number_format($responseHours, 1) }}h</small>
                                                    @endif
                                                @else
                                                    @if($hoursSinceCreation > $slaThreshold)
                                                        <span class="badge bg-danger">SLA Breached</span>
                                                    @elseif($hoursSinceCreation > 48)
                                                        <span class="badge bg-warning">At Risk</span>
                                                    @else
                                                        <span class="badge bg-success">On Track</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">{{ number_format($hoursSinceCreation, 1) }}h</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.organization-bookings.show', $request) }}" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if(!$request->organization->adviser)
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-warning"
                                                                onclick="reassignAdviser({{ $request->id }})"
                                                                title="Assign Adviser">
                                                            <i class="fas fa-user-plus"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="#" onclick="generateReport({{ $request->id }})">
                                                                <i class="fas fa-file-pdf"></i> Generate Report
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="viewAuditLog({{ $request->id }})">
                                                                <i class="fas fa-history"></i> Audit Log
                                                            </a></li>
                                                            @if($request->status === 'pending' && $request->isOverdue())
                                                                <li><a class="dropdown-item" href="#" onclick="escalateRequest({{ $request->id }})">
                                                                    <i class="fas fa-arrow-up"></i> Escalate
                                                                </a></li>
                                                            @endif
                                                        </ul>
                                                    </div>
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

<!-- Reassign Adviser Modal -->
<div class="modal fade" id="reassignAdviserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reassign Adviser</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reassignAdviserForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select New Adviser</label>
                        <select name="adviser_id" class="form-select" required>
                            <option value="">Choose an adviser...</option>
                            @foreach($advisers as $adviser)
                                <option value="{{ $adviser->id }}">{{ $adviser->name }} ({{ $adviser->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Reassignment</label>
                        <textarea name="reason" class="form-control" rows="3" 
                                  placeholder="Brief reason for the adviser change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Reassign Adviser
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

.btn-xs {
    padding: 0.125rem 0.25rem;
    font-size: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
function reassignAdviser(requestId) {
    const form = document.getElementById('reassignAdviserForm');
    form.action = `/admin/organization-bookings/${requestId}/reassign-adviser`;
    new bootstrap.Modal(document.getElementById('reassignAdviserModal')).show();
}

function selectAll() {
    document.getElementById('selectAllCheckbox').checked = true;
    document.querySelectorAll('.request-checkbox').forEach(cb => cb.checked = true);
}

function clearSelection() {
    document.getElementById('selectAllCheckbox').checked = false;
    document.querySelectorAll('.request-checkbox').forEach(cb => cb.checked = false);
}

function bulkActions() {
    const selected = document.querySelectorAll('.request-checkbox:checked');
    if (selected.length === 0) {
        alert('Please select at least one request.');
        return;
    }
    // Implement bulk actions modal/functionality
    alert('Bulk actions for ' + selected.length + ' requests');
}

function exportData() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open(`${window.location.pathname}?${params.toString()}`);
}

function systemSettings() {
    // Implement system settings modal
    alert('System settings functionality');
}

function generateReport(requestId) {
    window.open(`/admin/organization-bookings/${requestId}/report`, '_blank');
}

function viewAuditLog(requestId) {
    // Implement audit log view
    alert('Audit log for request #' + requestId);
}

function escalateRequest(requestId) {
    if (confirm('Escalate this request to highest priority?')) {
        // Implement escalation functionality
        alert('Request #' + requestId + ' has been escalated.');
    }
}

// Select All checkbox functionality
document.getElementById('selectAllCheckbox').addEventListener('change', function() {
    document.querySelectorAll('.request-checkbox').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>
@endpush