@extends('layouts.app')

@section('title', 'Booking Request Details')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Booking Request Details</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('staff.organization-bookings.index') }}">Organization Bookings</a>
                            </li>
                            <li class="breadcrumb-item active">Request #{{ $request->id }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    @if($request->isOverdue())
                        <a href="{{ route('staff.organization-bookings.overdue') }}" class="btn btn-warning me-2">
                            <i class="fas fa-exclamation-triangle"></i> Overdue List
                        </a>
                    @endif
                    <a href="{{ route('staff.organization-bookings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Details -->
                <div class="col-lg-8">
                    <!-- Request Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-list"></i> Request Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Request ID</h6>
                                    <p class="mb-3"><strong>#{{ $request->id }}</strong></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Date Submitted</h6>
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

                    <!-- Organization & People -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-users"></i> Organization & People
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="text-muted">Organization</h6>
                                    <p class="mb-3">
                                        <strong>{{ $request->organization->name }}</strong>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted">Requestor</h6>
                                    <p class="mb-3">
                                        <strong>{{ $request->requestor->name }}</strong><br>
                                        <small class="text-muted">{{ $request->requestor->email }}</small>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-muted">Assigned Adviser</h6>
                                    @if($request->organization->adviser)
                                        <p class="mb-3">
                                            <strong>{{ $request->organization->adviser->name }}</strong><br>
                                            <small class="text-muted">{{ $request->organization->adviser->email }}</small>
                                        </p>
                                    @else
                                        <p class="mb-3">
                                            <span class="badge bg-warning">No Adviser Assigned</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Processing History -->
                    @if($request->adviser_notes || $request->approved_by)
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-history"></i> Processing History
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($request->adviser_notes)
                                    <h6 class="text-muted">Adviser Notes</h6>
                                    <p class="mb-3">{{ $request->adviser_notes }}</p>
                                @endif
                                
                                @if($request->approved_by)
                                    <h6 class="text-muted">Processed By</h6>
                                    <p class="mb-1"><strong>{{ $request->approvedBy->name }}</strong></p>
                                    <small class="text-muted">
                                        {{ $request->approved_at ? $request->approved_at->format('F j, Y \a\t g:i A') : 
                                           ($request->rejected_at ? $request->rejected_at->format('F j, Y \a\t g:i A') : '') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Performance Metrics -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line"></i> Performance Metrics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">Time Since Submission</h6>
                                    <p class="mb-0">
                                        @php
                                            $hoursSince = $request->created_at->diffInHours(now());
                                            $daysSince = $request->created_at->diffInDays(now());
                                        @endphp
                                        {{ $daysSince }} day{{ $daysSince !== 1 ? 's' : '' }}, 
                                        {{ $hoursSince % 24 }} hour{{ ($hoursSince % 24) !== 1 ? 's' : '' }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted">SLA Status</h6>
                                    <p class="mb-0">
                                        @if($request->status !== 'pending')
                                            @php
                                                $responseTime = $request->approved_at ?? $request->rejected_at;
                                                $responseHours = $request->created_at->diffInHours($responseTime);
                                            @endphp
                                            @if($responseHours <= 72)
                                                <span class="badge bg-success">Within SLA ({{ number_format($responseHours, 1) }}h)</span>
                                            @else
                                                <span class="badge bg-danger">SLA Exceeded ({{ number_format($responseHours, 1) }}h)</span>
                                            @endif
                                        @else
                                            @if($request->isOverdue())
                                                <span class="badge bg-danger">SLA Exceeded</span>
                                            @else
                                                <span class="badge bg-warning">Within SLA</span>
                                            @endif
                                        @endif
                                    </p>
                                </div>
                                @if($request->adviser_notified_at)
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Adviser Notification</h6>
                                        <p class="mb-0">
                                            {{ $request->adviser_notified_at->format('M j, Y g:i A') }}
                                            <br>
                                            <small class="text-muted">{{ $request->adviser_notified_at->diffForHumans() }}</small>
                                        </p>
                                    </div>
                                @endif
                                @if($request->last_reminder_sent_at)
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Last Reminder Sent</h6>
                                        <p class="mb-0">
                                            {{ $request->last_reminder_sent_at->format('M j, Y g:i A') }}
                                            <br>
                                            <small class="text-muted">{{ $request->last_reminder_sent_at->diffForHumans() }}</small>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Actions Sidebar -->
                <div class="col-lg-4">
                    <!-- Status Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i> Current Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                @if($request->status === 'pending')
                                    @if($request->isOverdue())
                                        <div class="status-badge bg-danger text-white">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                            <h6 class="mt-2 mb-0">Overdue</h6>
                                            <small>Requires Escalation</small>
                                        </div>
                                    @else
                                        <div class="status-badge bg-warning text-dark">
                                            <i class="fas fa-clock fa-2x"></i>
                                            <h6 class="mt-2 mb-0">Pending Review</h6>
                                            <small>Awaiting Adviser</small>
                                        </div>
                                    @endif
                                @elseif($request->status === 'approved')
                                    <div class="status-badge bg-success text-white">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                        <h6 class="mt-2 mb-0">Approved</h6>
                                        <small>{{ $request->approved_at->format('M j, Y') }}</small>
                                    </div>
                                @elseif($request->status === 'rejected')
                                    <div class="status-badge bg-danger text-white">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                        <h6 class="mt-2 mb-0">Rejected</h6>
                                        <small>{{ $request->rejected_at->format('M j, Y') }}</small>
                                    </div>
                                @endif
                            </div>

                            @if($request->status === 'pending' && $request->isOverdue() && $request->organization->adviser)
                                <div class="d-grid">
                                    <button type="button" class="btn btn-warning" onclick="sendReminder()">
                                        <i class="fas fa-bell"></i> Send Reminder
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-tools"></i> Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($request->organization->adviser)
                                    <a href="mailto:{{ $request->organization->adviser->email }}?subject=Booking Request #{{ $request->id }} - {{ $request->organization->name }}&body=Dear {{ $request->organization->adviser->name }}, Please review booking request #{{ $request->id }} for {{ $request->organization->name }}." 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-envelope"></i> Email Adviser
                                    </a>
                                @endif
                                
                                <a href="mailto:{{ $request->requestor->email }}?subject=Update on Booking Request #{{ $request->id }}&body=Dear {{ $request->requestor->name }}, This is an update regarding your booking request for {{ $request->organization->name }}." 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-envelope"></i> Email Requestor
                                </a>
                                
                                <button type="button" class="btn btn-outline-secondary" onclick="generateReport()">
                                    <i class="fas fa-file-pdf"></i> Generate Report
                                </button>
                                
                                <button type="button" class="btn btn-outline-warning" onclick="escalateRequest()">
                                    <i class="fas fa-arrow-up"></i> Escalate to Admin
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history"></i> Timeline
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

<!-- Reminder Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('staff.organization-bookings.send-reminder', $request) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Overdue Request Alert</strong><br>
                        This request has been pending for {{ $request->created_at->diffInDays(now()) }} days.
                    </div>
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
.status-badge {
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
function sendReminder() {
    new bootstrap.Modal(document.getElementById('reminderModal')).show();
}

function generateReport() {
    window.open(`/staff/organization-bookings/{{ $request->id }}/report`, '_blank');
}

function escalateRequest() {
    if (confirm('Are you sure you want to escalate this request to administration?')) {
        fetch(`/staff/organization-bookings/{{ $request->id }}/escalate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                alert('Request has been escalated to administration.');
                location.reload();
            } else {
                alert('Error escalating request. Please try again.');
            }
        });
    }
}
</script>
@endpush