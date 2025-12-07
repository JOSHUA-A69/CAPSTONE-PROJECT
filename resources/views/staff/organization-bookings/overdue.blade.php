@extends('layouts.app')

@section('title', 'Overdue Booking Requests')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1 text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Overdue Booking Requests
                    </h2>
                    <p class="text-muted mb-0">Requests pending adviser action for over 72 hours</p>
                </div>
                <div>
                    <a href="{{ route('staff.organization-bookings.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to All Requests
                    </a>
                </div>
            </div>

            @if($overdueRequests->isEmpty())
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                        <h4 class="text-success">Great news!</h4>
                        <p class="text-muted">No overdue requests at this time. All requests are being processed within the expected timeframe.</p>
                        <a href="{{ route('staff.organization-bookings.index') }}" class="btn btn-primary">
                            <i class="fas fa-list"></i> View All Requests
                        </a>
                    </div>
                </div>
            @else
                <!-- Quick Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">
                                    <i class="fas fa-info-circle text-info"></i> Quick Actions
                                </h6>
                                <p class="text-muted small mb-0">
                                    {{ $overdueRequests->count() }} request{{ $overdueRequests->count() !== 1 ? 's' : '' }} 
                                    require{{ $overdueRequests->count() === 1 ? 's' : '' }} immediate attention
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <button type="button" class="btn btn-warning me-2" onclick="sendBulkReminders()">
                                    <i class="fas fa-bell"></i> Send All Reminders
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="exportOverdue()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overdue Requests Table -->
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Overdue Requests ({{ $overdueRequests->count() }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>Request</th>
                                        <th>Organization</th>
                                        <th>Adviser</th>
                                        <th>Days Overdue</th>
                                        <th>Last Reminder</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($overdueRequests as $request)
                                        @php
                                            $daysOverdue = $request->created_at->diffInDays(now()) - 3; // 3 days is the SLA
                                            $urgencyClass = $daysOverdue > 7 ? 'table-danger' : ($daysOverdue > 5 ? 'table-warning' : '');
                                        @endphp
                                        <tr class="{{ $urgencyClass }}">
                                            <td>
                                                <input type="checkbox" class="form-check-input request-checkbox" 
                                                       value="{{ $request->id }}">
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>#{{ $request->id }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        by {{ $request->requestor->name }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $request->organization->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ Str::limit($request->purpose, 30) }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($request->organization->adviser)
                                                    <div>
                                                        <strong>{{ $request->organization->adviser->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $request->organization->adviser->email }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="badge bg-warning">No Adviser Assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    @if($daysOverdue > 7)
                                                        <span class="badge bg-danger fs-6">
                                                            {{ $daysOverdue }} days
                                                        </span>
                                                        <br>
                                                        <small class="text-danger">Critical</small>
                                                    @elseif($daysOverdue > 5)
                                                        <span class="badge bg-warning fs-6">
                                                            {{ $daysOverdue }} days
                                                        </span>
                                                        <br>
                                                        <small class="text-warning">High</small>
                                                    @else
                                                        <span class="badge bg-orange fs-6">
                                                            {{ $daysOverdue }} days
                                                        </span>
                                                        <br>
                                                        <small class="text-orange">Medium</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($request->last_reminder_sent_at)
                                                    <div>
                                                        {{ $request->last_reminder_sent_at->format('M j, Y') }}
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $request->last_reminder_sent_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('staff.organization-bookings.show', $request) }}" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-warning"
                                                            onclick="sendReminder({{ $request->id }})"
                                                            title="Send Reminder">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                    @if($request->organization->adviser)
                                                        <a href="mailto:{{ $request->organization->adviser->email }}?subject=Urgent: Overdue Booking Request #{{ $request->id }}&body=Dear {{ $request->organization->adviser->name }}, Please review the overdue booking request for {{ $request->organization->name }}." 
                                                           class="btn btn-sm btn-outline-info"
                                                           title="Send Email">
                                                            <i class="fas fa-envelope"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-danger">{{ $overdueRequests->where('created_at', '<', now()->subDays(7))->count() }}</h4>
                                <p class="mb-0 small text-muted">Critical (7+ days)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-warning">{{ $overdueRequests->where('created_at', '<', now()->subDays(5))->where('created_at', '>=', now()->subDays(7))->count() }}</h4>
                                <p class="mb-0 small text-muted">High (5-7 days)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-orange">{{ $overdueRequests->where('created_at', '>=', now()->subDays(5))->count() }}</h4>
                                <p class="mb-0 small text-muted">Medium (3-5 days)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-info">{{ number_format($overdueRequests->avg(function($req) { return $req->created_at->diffInDays(now()) - 3; }), 1) }}</h4>
                                <p class="mb-0 small text-muted">Avg Days Overdue</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Overdue Request Alert</strong><br>
                        This request has been pending adviser action for an extended period.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority Level</label>
                        <select name="priority" class="form-select" required>
                            <option value="normal">Normal Reminder</option>
                            <option value="urgent">Urgent - Escalated</option>
                            <option value="critical">Critical - Requires Immediate Action</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" 
                                  placeholder="Add any additional context, urgency notes, or escalation information..."></textarea>
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

<!-- Bulk Reminder Modal -->
<div class="modal fade" id="bulkReminderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Bulk Reminders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkReminderForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Send reminder emails to advisers for all overdue requests?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This will send reminders to {{ $overdueRequests->count() }} overdue request(s).
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message for All Reminders</label>
                        <textarea name="message" class="form-control" rows="3" 
                                  placeholder="This message will be included in all reminder emails..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-bell"></i> Send All Reminders
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.table-danger {
    --bs-table-bg: #f8d7da;
    --bs-table-border-color: #f1aeb5;
}

.text-orange {
    color: #fd7e14 !important;
}

.bg-orange {
    background-color: #fd7e14 !important;
}

.badge.fs-6 {
    font-size: 1rem !important;
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

function sendBulkReminders() {
    new bootstrap.Modal(document.getElementById('bulkReminderModal')).show();
}

function exportOverdue() {
    window.open(`${window.location.pathname}?export=csv`);
}

// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Auto-refresh for real-time monitoring
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 180000); // Refresh every 3 minutes for overdue page
</script>
@endpush