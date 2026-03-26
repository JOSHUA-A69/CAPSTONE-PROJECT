@extends('layouts.app')

@section('title', 'Organization Booking Calendar')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Organization Booking Calendar</h2>
            <p class="text-gray-600 dark:text-gray-400">Manage your organization activity bookings and confirmations</p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap gap-3 mb-8">
            <button type="button" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold shadow-sm" onclick="setActiveTab(this); filterCalendar('')">All</button>
            <button type="button" class="px-4 py-2 rounded-xl bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700" onclick="setActiveTab(this); filterCalendar('pending')">Pending Confirmation</button>
            <button type="button" class="px-4 py-2 rounded-xl bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center" onclick="setActiveTab(this); filterCalendar('approved')">Upcoming <span class="ml-2 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full">2</span></button>
            <button type="button" class="px-4 py-2 rounded-xl bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700" onclick="setActiveTab(this); filterCalendar('past')">Past Services</button>
            <button type="button" class="px-4 py-2 rounded-xl bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center" onclick="setActiveTab(this); filterCalendar('rejected')">Declined Services <span class="ml-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">2</span></button>
        </div>

        <!-- Calendar Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div class="flex items-center gap-2">
                <button type="button" class="p-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition" id="prevMonth">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button type="button" class="px-4 py-2 rounded-lg border border-blue-500 text-blue-700 bg-blue-50 dark:bg-blue-900/20 font-semibold" id="currentMonth">
                    <span id="monthYear"></span>
                </button>
                <button type="button" class="p-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition" id="nextMonth">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-yellow-400 rounded"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-500 rounded"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Confirmed</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-red-500 rounded"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Declined</span>
                </div>
            </div>
        </div>

        <!-- Calendar Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
            <div id="calendar" class="min-h-[600px]"></div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-white dark:bg-gray-800 rounded-2xl border-0 shadow-2xl">
            <div class="modal-header border-0 p-6 pb-0">
                <h5 class="modal-title text-xl font-semibold text-gray-900 dark:text-white">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-6" id="eventDetails">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer border-0 p-6 pt-0 flex justify-center">
                <button type="button" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition mr-2" data-bs-dismiss="modal">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Close
                </button>
                <a href="#" id="viewDetailsBtn" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium shadow-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Full Details
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/main.min.css' rel='stylesheet' />

<style>
/* Calendar container styling */
.fc {
    background: transparent;
    border: none;
}

.fc-header-toolbar {
    display: none !important; /* Hide default header since we have custom controls */
}

.fc-view-harness {
    background: transparent;
}

.fc-daygrid {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}

.fc-scrollgrid {
    border: none !important;
}

.fc-col-header-cell {
    background: #f8fafc !important;
    border-color: #e5e7eb !important;
    padding: 1rem !important;
    font-weight: 600 !important;
    color: #6b7280 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    font-size: 0.75rem !important;
}

.fc-daygrid-day {
    border-color: #e5e7eb !important;
    min-height: 100px !important;
    background: white;
}

.fc-daygrid-day-number {
    padding: 0.75rem !important;
    font-weight: 500 !important;
    color: #374151 !important;
    font-size: 0.875rem !important;
}

.fc-day-today {
    background-color: #eff6ff !important;
}

.fc-day-today .fc-daygrid-day-number {
    background: #3b82f6 !important;
    color: white !important;
    border-radius: 50% !important;
    width: 2rem !important;
    height: 2rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 0.25rem !important;
}

/* Event styling - matching the service cards */
.fc-event {
    border: none !important;
    margin: 2px 4px !important;
    font-size: 0.75rem !important;
    border-radius: 8px !important;
    padding: 4px 8px !important;
    font-weight: 500 !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
    border-left: 4px solid transparent !important;
    cursor: pointer !important;
}

.fc-event-pending {
    background-color: #fef3c7 !important;
    color: #92400e !important;
    border-left-color: #fbbf24 !important;
}

.fc-event-approved {
    background-color: #d1fae5 !important;
    color: #065f46 !important;
    border-left-color: #10b981 !important;
}

.fc-event-rejected {
    background-color: #f3f4f6 !important;
    color: #374151 !important;
    border-left-color: #6b7280 !important;
}

.fc-event-overdue {
    background-color: #fee2e2 !important;
    color: #991b1b !important;
    border-left-color: #dc2626 !important;
    animation: pulse 2s infinite;
}

.fc-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
}

.fc-event-title {
    font-weight: 600 !important;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

.fc-daygrid-event-harness {
    margin-top: 2px;
}

/* Dark mode support */
.dark .fc-col-header-cell {
    background: #374151 !important;
    color: #d1d5db !important;
}

.dark .fc-daygrid-day {
    background: #1f2937 !important;
}

.dark .fc-daygrid-day-number {
    color: #f9fafb !important;
}

.dark .fc-day-today {
    background-color: #1e40af !important;
}

.dark .fc-daygrid {
    border-color: #374151 !important;
}

.dark .fc-col-header-cell {
    border-color: #374151 !important;
}

.dark .fc-daygrid-day {
    border-color: #374151 !important;
}
</style>
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/main.min.js'></script>

<script>
let calendar;
let currentFilter = '';

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        timeZone: 'Asia/Manila',
        height: 'auto',
        headerToolbar: {
            left: '',
            center: '',
            right: ''
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('{{ route("adviser.organization-bookings.calendar-data") }}?' + new URLSearchParams({
                start: fetchInfo.startStr,
                end: fetchInfo.endStr,
                filter: currentFilter
            }))
            .then(response => response.json())
            .then(data => successCallback(data))
            .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        eventClassNames: function(info) {
            const status = info.event.extendedProps.status;
            const isOverdue = info.event.extendedProps.is_overdue;
            
            if (status === 'pending' && isOverdue) {
                return ['fc-event-overdue'];
            } else if (status === 'pending') {
                return ['fc-event-pending'];
            } else if (status === 'approved') {
                return ['fc-event-approved'];
            } else if (status === 'rejected') {
                return ['fc-event-rejected'];
            }
            
            return [];
        },
        datesSet: function(info) {
            document.getElementById('monthYear').textContent = 
                info.view.currentStart.toLocaleDateString('en-US', { 
                    month: 'long', 
                    year: 'numeric' 
                });
        }
    });

    calendar.render();

    // Month navigation
    document.getElementById('prevMonth').addEventListener('click', function() {
        calendar.prev();
    });

    document.getElementById('nextMonth').addEventListener('click', function() {
        calendar.next();
    });

    document.getElementById('currentMonth').addEventListener('click', function() {
        calendar.today();
    });
    // Initialize first tab as active
    const firstTab = document.querySelector('[onclick^="setActiveTab"]');
    if (firstTab) setActiveTab(firstTab);
});

function filterCalendar(filter) {
    currentFilter = filter;
    calendar.refetchEvents();
    
    // Reflected by setActiveTab styling
}

function setActiveTab(button) {
    document.querySelectorAll('[onclick^="setActiveTab"]').forEach(btn => {
        btn.classList.remove('bg-indigo-600','text-white','font-semibold');
        btn.classList.add('bg-white','dark:bg-gray-800','text-gray-800','dark:text-gray-200','border','border-gray-200','dark:border-gray-700');
    });
    button.classList.remove('bg-white','dark:bg-gray-800','text-gray-800','dark:text-gray-200','border','border-gray-200','dark:border-gray-700');
    button.classList.add('bg-indigo-600','text-white','font-semibold');
}

function showEventDetails(event) {
    const dateStr = new Date(event.start).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
    const statusColor = getStatusColor(event.extendedProps.status, event.extendedProps.is_overdue);
    const statusText = getStatusText(event.extendedProps.status, event.extendedProps.is_overdue);
    const location = event.extendedProps.location || event.extendedProps.venue || 'TBD';
    const organization = event.extendedProps.organization || 'Organization';
    const purpose = event.extendedProps.purpose || 'No description provided.';
    const safeTitle = escapeHtml(event.title || 'Organization Activity');
    const safeStatusText = escapeHtml(statusText);
    const safeDateStr = escapeHtml(dateStr);
    const safeLocation = escapeHtml(location);
    const safeOrganization = escapeHtml(organization);
    const safePurpose = escapeHtml(purpose);
    const detailsId = Number.parseInt(event.extendedProps.id, 10);
    const safeDetailsId = Number.isNaN(detailsId) ? '' : String(detailsId);

    const details = `
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-indigo-100 dark:border-gray-700 shadow-md">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${safeTitle}</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-${statusColor === 'success' ? 'green-100' : statusColor === 'warning' ? 'yellow-100' : statusColor === 'danger' ? 'red-100' : 'gray-100'} text-${statusColor === 'success' ? 'green-700' : statusColor === 'warning' ? 'yellow-700' : statusColor === 'danger' ? 'red-700' : 'gray-700'}">${safeStatusText}</span>
                </div>

                <div class="flex items-center text-gray-600 dark:text-gray-300 gap-2 mb-4">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>${safeDateStr}</span>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414a4 4 0 10-5.657 5.657l4.243 4.243a8 8 0 1011.314-11.314l-4.243 4.243z"></path>
                        </svg>
                        <span>${safeLocation}</span>
                        <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">Custom</span>
                    </div>
                    <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>${safeOrganization}</span>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-4 text-gray-600 dark:text-gray-300 mb-4">
                    ${safePurpose}
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="/adviser/organization-bookings/${safeDetailsId}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium shadow-sm transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Full Details
                    </a>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('eventDetails').innerHTML = details;
    document.getElementById('viewDetailsBtn').href = 
        `/adviser/organization-bookings/${safeDetailsId}`;
    
    new bootstrap.Modal(document.getElementById('eventModal')).show();
}

function getStatusColor(status, isOverdue) {
    if (status === 'pending' && isOverdue) return 'danger';
    if (status === 'pending') return 'warning';
    if (status === 'approved') return 'success';
    if (status === 'rejected') return 'secondary';
    return 'light';
}

function getStatusText(status, isOverdue) {
    if (status === 'pending' && isOverdue) return 'Overdue';
    if (status === 'pending') return 'Pending';
    if (status === 'approved') return 'Approved';
    if (status === 'rejected') return 'Rejected';
    return status;
}
</script>
@endpush