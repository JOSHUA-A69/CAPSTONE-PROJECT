@extends('layouts.app')

@section('title', 'Booking Request Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Booking Request Management</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.organization-bookings.index') }}">Organization Bookings</a>
                            </li>
                            <li class="breadcrumb-item active">Request #{{ $request->id }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.organization-bookings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-tools"></i> Admin Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="generateReport()">
                                    <i class="fas fa-file-pdf"></i> Generate Report
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="viewAuditLog()">
                                    <i class="fas fa-history"></i> View Audit Log
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="escalateRequest()">
                                    <i class="fas fa-arrow-up"></i> Escalate Priority
                                </a></li>
                                @if(!$request->organization->adviser)
                                    <li><a class="dropdown-item" href="#" onclick="reassignAdviser()">
                                        <i class="fas fa-user-plus"></i> Assign Adviser
                                    </a></li>
                                @else
                                    <li><a class="dropdown-item" href="#" onclick="reassignAdviser()">
                                        <i class="fas fa-user-edit"></i> Reassign Adviser
                                    </a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="archiveRequest()">
                                    <i class="fas fa-archive"></i> Archive Request
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Request Overview -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-list"></i> Request Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Request ID</h6>
                                    <p class="mb-3">
                                        <strong>#{{ $request->id }}</strong>
                                        <span class="badge bg-info ms-2">{{ ucfirst($request->status) }}</span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Submission Date</h6>
                                    <p class="mb-3">{{ $request->created_at->format('F j, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <h6 class="text-muted">Purpose</h6>
                                    <p>{{ $request->purpose }}</p>
                                </div>
                                <div class="col-12">
                                    <h6 class="text-muted">Activity Details</h6>
                                    <p>{{ $request->activity_details }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stakeholders -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users"></i> Stakeholders
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-building fa-2x text-primary mb-2"></i>
                                        <h6 class="mb-1">Organization</h6>
                                        <strong>{{ $request->organization->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $request->organization->type ?? 'N/A' }}</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-user fa-2x text-success mb-2"></i>
                                        <h6 class="mb-1">Requestor</h6>
                                        <strong>{{ $request->requestor->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $request->requestor->email }}</small>
                                        <br>
                                        <a href="mailto:{{ $request->requestor->email }}" class="btn btn-sm btn-outline-primary mt-1">
                                            <i class="fas fa-envelope"></i> Contact
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-user-tie fa-2x text-warning mb-2"></i>
                                        <h6 class="mb-1">Assigned Adviser</h6>
                                        @if($request->organization->adviser)
                                            <strong>{{ $request->organization->adviser->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $request->organization->adviser->email }}</small>
                                            <br>
                                            <div class="btn-group mt-1" role="group">
                                                <a href="mailto:{{ $request->organization->adviser->email }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-envelope"></i> Email
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reassignAdviser()">
                                                    <i class="fas fa-user-edit"></i> Change
                                                </button>
                                            </div>
                                        @else
                                            <span class="badge bg-danger">No Adviser Assigned</span>
                                            <br>
                                            <button type="button" class="btn btn-sm btn-warning mt-2" onclick="reassignAdviser()">
                                                <i class="fas fa-user-plus"></i> Assign Adviser
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Processing Notes -->
                    @if($request->adviser_notes || $request->approved_by)
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-sticky-note"></i> Processing Notes
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($request->adviser_notes)
                                    <h6 class="text-muted">Adviser Notes</h6>
                                    <div class="alert alert-light">
                                        {{ $request->adviser_notes }}
                                    </div>
                                @endif
                                
                                @if($request->approved_by)
                                    <h6 class="text-muted">Processing Information</h6>
                                    <p class="mb-1">
                                        <strong>Processed by:</strong> {{ $request->approvedBy->name }}
                                    </p>
                                    <small class="text-muted">
                                        {{ $request->approved_at ? 'Approved on ' . $request->approved_at->format('F j, Y \a\t g:i A') : 
                                           ($request->rejected_at ? 'Rejected on ' . $request->rejected_at->format('F j, Y \a\t g:i A') : '') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- System Analytics -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line"></i> System Analytics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">Response Time Analysis</h6>
                                    @if($request->status !== 'pending')
                                        @php
                                            $responseTime = $request->approved_at ?? $request->rejected_at;
                                            $responseHours = $request->created_at->diffInHours($responseTime);
                                            $slaThreshold = 72;
                                        @endphp
                                        <p class="mb-1">
                                            <strong>Response Time:</strong> {{ number_format($responseHours, 1) }} hours
                                        </p>
                                        <p class="mb-0">
                                            <strong>SLA Status:</strong>
                                            @if($responseHours <= $slaThreshold)
                                                <span class="badge bg-success">Within SLA</span>
                                            @else
                                                <span class="badge bg-danger">SLA Breached</span>
                                            @endif
                                        </p>
                                    @else
                                        @php
                                            $hoursPending = $request->created_at->diffInHours(now());
                                        @endphp
                                        <p class="mb-1">
                                            <strong>Time Pending:</strong> {{ number_format($hoursPending, 1) }} hours
                                        </p>
                                        <p class="mb-0">
                                            <strong>Current Status:</strong>
                                            @if($hoursPending > 72)
                                                <span class="badge bg-danger">SLA Breached</span>
                                            @elseif($hoursPending > 48)
                                                <span class="badge bg-warning">At Risk</span>
                                            @else
                                                <span class="badge bg-success">On Track</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">System Tracking</h6>
                                    <p class="mb-1">
                                        <strong>Notifications Sent:</strong> {{ $request->adviser_notified_at ? '1' : '0' }}
                                        @if($request->last_reminder_sent_at)
                                            + Reminders
                                        @endif
                                    </p>
                                    <p class="mb-0">
                                        <strong>Last Update:</strong> {{ $request->updated_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                                <div class="col-12">
                                    <h6 class="text-muted">Administrative Flags</h6>
                                    <div class="d-flex gap-2 flex-wrap">
                                        @if($request->isOverdue())
                                            <span class="badge bg-danger">Overdue</span>
                                        @endif
                                        @if(!$request->organization->adviser)
                                            <span class="badge bg-warning">No Adviser</span>
                                        @endif
                                        @if($request->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($request->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-primary">Active</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Status Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-flag"></i> Current Status
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            @if($request->status === 'pending')
                                @if($request->isOverdue())
                                    <div class="status-display bg-danger text-white">
                                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                        <h4>OVERDUE</h4>
                                        <p class="mb-0">Requires Immediate Attention</p>
                                    </div>
                                @else
                                    <div class="status-display bg-warning text-dark">
                                        <i class="fas fa-clock fa-3x mb-3"></i>
                                        <h4>PENDING REVIEW</h4>
                                        <p class="mb-0">Awaiting Adviser Decision</p>
                                    </div>
                                @endif
                            @elseif($request->status === 'approved')
                                <div class="status-display bg-success text-white">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <h4>APPROVED</h4>
                                    <p class="mb-0">{{ $request->approved_at->format('M j, Y') }}</p>
                                </div>
                            @elseif($request->status === 'rejected')
                                <div class="status-display bg-danger text-white">
                                    <i class="fas fa-times-circle fa-3x mb-3"></i>
                                    <h4>REJECTED</h4>
                                    <p class="mb-0">{{ $request->rejected_at->format('M j, Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt"></i> Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($request->status === 'pending' && $request->isOverdue())
                                    <button type="button" class="btn btn-warning" onclick="sendUrgentReminder()">
                                        <i class="fas fa-bell"></i> Send Urgent Reminder
                                    </button>
                                @endif
                                
                                @if(!$request->organization->adviser)
                                    <button type="button" class="btn btn-primary" onclick="reassignAdviser()">
                                        <i class="fas fa-user-plus"></i> Assign Adviser
                                    </button>
                                @endif
                                
                                <button type="button" class="btn btn-outline-primary" onclick="generateReport()">
                                    <i class="fas fa-file-pdf"></i> Generate Report
                                </button>
                                
                                <button type="button" class="btn btn-outline-info" onclick="viewAuditLog()">
                                    <i class="fas fa-history"></i> View Audit Trail
                                </button>
                                
                                @if($request->status === 'pending')
                                    <button type="button" class="btn btn-outline-warning" onclick="escalateRequest()">
                                        <i class="fas fa-arrow-up"></i> Escalate Priority
                                    </button>
                                @endif
                                
                                <hr>
                                
                                <button type="button" class="btn btn-outline-danger" onclick="archiveRequest()">
                                    <i class="fas fa-archive"></i> Archive Request
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-timeline"></i> Activity Timeline
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Request Submitted</h6>
                                        <p class="text-muted small mb-0">
                                            {{ $request->created_at->format('M j, Y g:i A') }}
                                        </p>
                                        <small class="text-muted">by {{ $request->requestor->name }}</small>
                                    </div>
                                </div>

                                @if($request->adviser_notified_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Adviser Notified</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $request->adviser_notified_at->format('M j, Y g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($request->last_reminder_sent_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Reminder Sent</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $request->last_reminder_sent_at->format('M j, Y g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($request->status === 'approved')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Request Approved</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $request->approved_at->format('M j, Y g:i A') }}
                                            </p>
                                            <small class="text-muted">by {{ $request->approvedBy->name }}</small>
                                        </div>
                                    </div>
                                @elseif($request->status === 'rejected')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Request Rejected</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $request->rejected_at->format('M j, Y g:i A') }}
                                            </p>
                                            <small class="text-muted">by {{ $request->approvedBy->name }}</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
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
                <h5 class="modal-title">
                    {{ $request->organization->adviser ? 'Reassign' : 'Assign' }} Adviser
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.organization-bookings.reassign-adviser', $request) }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($request->organization->adviser)
                        <div class="alert alert-info">
                            <strong>Current Adviser:</strong> {{ $request->organization->adviser->name }}
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label">Select New Adviser <span class="text-danger">*</span></label>
                        <select name="adviser_id" class="form-select" required>
                            <option value="">Choose an adviser...</option>
                            @foreach($advisers as $adviser)
                                <option value="{{ $adviser->id }}" 
                                        {{ $request->organization->adviser_id == $adviser->id ? 'selected' : '' }}>
                                    {{ $adviser->name }} ({{ $adviser->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason for {{ $request->organization->adviser ? 'Reassignment' : 'Assignment' }}</label>
                        <textarea name="reason" class="form-control" rows="3" 
                                  placeholder="Provide a brief explanation for this action..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> 
                        {{ $request->organization->adviser ? 'Reassign' : 'Assign' }} Adviser
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.status-display {
    padding: 2rem;
    border-radius: 12px;
    border: 2px solid rgba(0,0,0,0.1);
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -0.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    margin-left: 1.5rem;
}
</style>
@endpush

@push('scripts')
<script>
function reassignAdviser() {
    new bootstrap.Modal(document.getElementById('reassignAdviserModal')).show();
}

function sendUrgentReminder() {
    if (confirm('Send an urgent reminder to the adviser?')) {
        fetch(`/admin/organization-bookings/{{ $request->id }}/urgent-reminder`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => {
            if (response.ok) {
                alert('Urgent reminder sent successfully.');
                location.reload();
            } else {
                alert('Error sending reminder. Please try again.');
            }
        });
    }
}

function generateReport() {
    window.open(`/admin/organization-bookings/{{ $request->id }}/report`, '_blank');
}

function viewAuditLog() {
    // Implement audit log modal or page
    alert('Audit log functionality - showing all actions taken on this request');
}

function escalateRequest() {
    if (confirm('Escalate this request to highest priority?')) {
        fetch(`/admin/organization-bookings/{{ $request->id }}/escalate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => {
            if (response.ok) {
                alert('Request has been escalated to highest priority.');
                location.reload();
            } else {
                alert('Error escalating request. Please try again.');
            }
        });
    }
}

function archiveRequest() {
    if (confirm('Are you sure you want to archive this request? This action cannot be undone.')) {
        fetch(`/admin/organization-bookings/{{ $request->id }}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => {
            if (response.ok) {
                alert('Request has been archived.');
                window.location.href = '{{ route("admin.organization-bookings.index") }}';
            } else {
                alert('Error archiving request. Please try again.');
            }
        });
    }
}
</script>
@endpush