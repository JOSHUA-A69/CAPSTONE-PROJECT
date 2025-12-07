@extends('layouts.app')

@section('title', 'Organization Booking Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Organization Booking Reports & Analytics</h2>
                <div>
                    <a href="{{ route('admin.organization-bookings.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Management
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportReport('excel')">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Date Range Selector -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Report Period</label>
                            <select name="period" class="form-select" onchange="this.form.submit()">
                                <option value="week" {{ request('period', 'week') === 'week' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="quarter" {{ request('period') === 'quarter' ? 'selected' : '' }}>Last 90 Days</option>
                                <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Last 365 Days</option>
                                <option value="custom" {{ request('period') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        @if(request('period') === 'custom')
                            <div class="col-md-2">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" 
                                       value="{{ request('start_date') }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" 
                                       value="{{ request('end_date') }}" onchange="this.form.submit()">
                            </div>
                        @endif
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
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $metrics['total'] ?? 0 }}</h3>
                            <p class="mb-0 small">Total Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $metrics['approved'] ?? 0 }}</h3>
                            <p class="mb-0 small">Approved</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $metrics['rejected'] ?? 0 }}</h3>
                            <p class="mb-0 small">Rejected</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-warning text-dark shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $metrics['pending'] ?? 0 }}</h3>
                            <p class="mb-0 small">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ number_format($metrics['approval_rate'] ?? 0, 1) }}%</h3>
                            <p class="mb-0 small">Approval Rate</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                    <div class="card bg-secondary text-white shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $metrics['avg_response_hours'] ?? 'N/A' }}</h3>
                            <p class="mb-0 small">Avg Response (hrs)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Charts Column -->
                <div class="col-lg-8">
                    <!-- Request Volume Chart -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line"></i> Request Volume Over Time
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="requestVolumeChart" style="height: 300px;"></canvas>
                        </div>
                    </div>

                    <!-- Status Distribution -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie"></i> Status Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="statusPieChart" style="height: 250px;"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Summary Statistics</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Total Requests:</td>
                                            <td><strong>{{ $metrics['total'] ?? 0 }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Approval Rate:</td>
                                            <td><strong>{{ number_format($metrics['approval_rate'] ?? 0, 1) }}%</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Rejection Rate:</td>
                                            <td><strong>{{ number_format($metrics['rejection_rate'] ?? 0, 1) }}%</strong></td>
                                        </tr>
                                        <tr>
                                            <td>SLA Compliance:</td>
                                            <td><strong>{{ number_format($metrics['sla_compliance'] ?? 0, 1) }}%</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Avg Response Time:</td>
                                            <td><strong>{{ $metrics['avg_response_hours'] ?? 'N/A' }} hours</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Response Time Analysis -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Response Time Analysis
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="responseTimeChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Statistics Sidebar -->
                <div class="col-lg-4">
                    <!-- Top Organizations -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-building"></i> Organizations by Activity
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($topOrganizations->isEmpty())
                                <p class="text-muted">No data available for the selected period.</p>
                            @else
                                @foreach($topOrganizations as $org)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong>{{ $org->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $org->requests_count }} request{{ $org->requests_count !== 1 ? 's' : '' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @php
                                                $approvalRate = $org->requests_count > 0 ? 
                                                    ($org->approved_count / $org->requests_count) * 100 : 0;
                                            @endphp
                                            <span class="badge {{ $approvalRate >= 80 ? 'bg-success' : ($approvalRate >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ number_format($approvalRate, 1) }}%
                                            </span>
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="my-2">
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Adviser Performance -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user-tie"></i> Adviser Performance
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($adviserPerformance->isEmpty())
                                <p class="text-muted">No adviser data available.</p>
                            @else
                                @foreach($adviserPerformance as $adviser)
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong>{{ $adviser->name }}</strong>
                                            <small class="text-muted">{{ $adviser->total_requests }} requests</small>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Avg Response:</small>
                                                <br>
                                                <strong>{{ number_format($adviser->avg_response_hours, 1) }}h</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">SLA Compliance:</small>
                                                <br>
                                                <strong class="{{ $adviser->sla_compliance >= 90 ? 'text-success' : ($adviser->sla_compliance >= 70 ? 'text-warning' : 'text-danger') }}">
                                                    {{ number_format($adviser->sla_compliance, 1) }}%
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <hr>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- System Alerts -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle"></i> System Alerts
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($systemAlerts->isEmpty())
                                <div class="text-center text-success">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <p class="mb-0">All systems normal</p>
                                </div>
                            @else
                                @foreach($systemAlerts as $alert)
                                    <div class="alert alert-{{ $alert['level'] }} alert-sm">
                                        <i class="fas fa-{{ $alert['icon'] }}"></i>
                                        <strong>{{ $alert['title'] }}</strong><br>
                                        {{ $alert['message'] }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
<style>
.alert-sm {
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Request Volume Chart
const requestVolumeCtx = document.getElementById('requestVolumeChart').getContext('2d');
new Chart(requestVolumeCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData['volume']['labels'] ?? []) !!},
        datasets: [{
            label: 'Requests Submitted',
            data: {!! json_encode($chartData['volume']['data'] ?? []) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Status Pie Chart
const statusPieCtx = document.getElementById('statusPieChart').getContext('2d');
new Chart(statusPieCtx, {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Rejected', 'Pending', 'Overdue'],
        datasets: [{
            data: [
                {{ $metrics['approved'] ?? 0 }},
                {{ $metrics['rejected'] ?? 0 }},
                {{ $metrics['pending'] ?? 0 }},
                {{ $metrics['overdue'] ?? 0 }}
            ],
            backgroundColor: [
                '#198754',
                '#dc3545',
                '#ffc107',
                '#fd7e14'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Response Time Chart
const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
new Chart(responseTimeCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartData['response_time']['labels'] ?? []) !!},
        datasets: [{
            label: 'Average Response Time (hours)',
            data: {!! json_encode($chartData['response_time']['data'] ?? []) !!},
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Hours'
                }
            }
        }
    }
});

function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    window.open(`${window.location.pathname}?${params.toString()}`);
}

function resetFilters() {
    window.location.href = '{{ route("admin.organization-bookings.reports") }}';
}
</script>
@endpush