@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white flex items-center gap-3">
            <div class="p-3 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            My Calendar
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400 text-lg">View and manage your upcoming reservations and scheduled activities</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-2 border-emerald-100 dark:border-emerald-800 p-8 hover:shadow-2xl transition-shadow duration-300">
        <div id="requestorCalendar"></div>
    </div>

    @php $hasSchedules = isset($schedules) && $schedules && $schedules->count() > 0; @endphp
    @if($reservations->isEmpty() && ! $hasSchedules)
        <div class="mt-6 p-6 bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/30 dark:to-amber-900/30 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-xl shadow-md">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-yellow-800 dark:text-yellow-200 font-semibold">No upcoming reservations or scheduled activities found.</p>
            </div>
        </div>
    @endif
</div>
@endsection


@push('styles')
<style>
    /* Enhanced calendar event styling */
    .fc-event-content-enhanced {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .fc-event:hover .fc-event-content-enhanced {
        transform: translateY(-1px);
    }
    
    .fc-event {
        border-radius: 6px !important;
        border-width: 2px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }
    
    .fc-event:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
        z-index: 10;
    }
    
    .fc-daygrid-event {
        margin: 2px 0;
        padding: 2px;
    }
    
    /* Calendar header styling */
    .fc .fc-toolbar-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #047857;
    }
    
    .dark .fc .fc-toolbar-title {
        color: #34d399;
    }
    
    .fc .fc-button-primary {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .fc .fc-button-primary:hover {
        background-color: #059669 !important;
        border-color: #059669 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
    }
    
    .fc .fc-button-primary:disabled {
        background-color: #6ee7b7 !important;
        border-color: #6ee7b7 !important;
        opacity: 0.6;
    }
    
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #e5e7eb;
    }
    
    .dark .fc-theme-standard td, .dark .fc-theme-standard th {
        border-color: #374151;
    }
    
    .fc .fc-col-header-cell {
        background-color: #f3f4f6;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #374151;
        padding: 12px 4px;
    }
    
    .dark .fc .fc-col-header-cell {
        background-color: #1f2937;
        color: #d1d5db;
    }
    
    .fc .fc-daygrid-day-top {
        padding: 4px;
        font-weight: 600;
    }
    
    .fc .fc-daygrid-day.fc-day-today {
        background-color: #ecfdf5 !important;
    }
    
    .dark .fc .fc-daygrid-day.fc-day-today {
        background-color: #064e3b !important;
    }
    
    .fc .fc-daygrid-day-number {
        color: #1f2937;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .dark .fc .fc-daygrid-day-number {
        color: #f3f4f6;
    }
    
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background-color: #10b981;
        color: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', initializeRequestorCalendar);

    function initializeRequestorCalendar() {
        if (typeof window.Calendar === 'undefined') {
            setTimeout(initializeRequestorCalendar, 150);
            return;
        }

        const reservationNode = document.getElementById('requestor-reservations-json');
        const scheduleNode = document.getElementById('requestor-schedules-json');
        const calendarHost = document.getElementById('requestorCalendar');
        const sweetAlert = window.Swal;

        if (!reservationNode || !scheduleNode || !calendarHost) {
            return;
        }

        const rawReservations = JSON.parse(reservationNode.textContent || '[]');
        const rawSchedules = JSON.parse(scheduleNode.textContent || '[]');
        const APP_TIMEZONE = "{{ config('app.timezone') }}";

        const CATEGORY_COLORS = {
            institutional: '#7C3AED',
            nonInstitutional: '#2563EB',
            other: '#94A3B8'
        };

        const CATEGORY_LABELS = {
            institutional: 'Institutional',
            nonInstitutional: 'Non-Institutional',
            other: 'Other'
        };

        const ENTRY_LABELS = {
            reservation: 'Upcoming Reservation',
            schedule: 'Plotted Schedule'
        };

        function extractDatePart(value) {
            if (!value) return null;
            if (value instanceof Date) {
               // Use local time components explicitly to avoid UTC shift
               const y = value.getFullYear();
               const m = String(value.getMonth() + 1).padStart(2, '0');
               const d = String(value.getDate()).padStart(2, '0');
               return `${y}-${m}-${d}`;
            }
            if (typeof value === 'string') {
                if (value.includes('T')) {
                    return value.split('T')[0];
                }
                if (value.includes(' ')) {
                    return value.split(' ')[0];
                }
            }
            return value;
        }

        function extractTimePart(value) {
            if (!value) return null;
            
            // Prefer string manipulation to preserve raw time if possible
            if (typeof value === 'string') {
                if (value.includes('T')) {
                    // Handle ISO strings (e.g., 2026-02-15T06:30:00+08:00)
                    const timeFragment = value.split('T')[1] || '';
                    // Remove Z or timezone offsets like +08:00 or -05:00
                    return timeFragment.split(/[Z+\-]/)[0];
                }
                if (value.includes(' ')) {
                    // Handle SQL datetime (2026-02-15 06:30:00)
                    return value.split(' ')[1] || null;
                }
                // If it's just a time string "06:30:00"
                if (value.includes(':')) {
                    return value;
                }
            }

            // Fallback for Date objects
            const date = value instanceof Date ? value : new Date(value);
            if (!Number.isNaN(date.getTime())) {
                const h = String(date.getHours()).padStart(2, '0');
                const m = String(date.getMinutes()).padStart(2, '0');
                const s = String(date.getSeconds()).padStart(2, '0');
                return `${h}:${m}:${s}`;
            }
            return null;
        }

        function combineDateAndTime(dateValue, timeValue) {
            const dateOnly = extractDatePart(dateValue);
            if (!dateOnly) return null;
            if (!timeValue) return dateOnly;
            return `${dateOnly}T${timeValue}`;
        }

        function resolveCategory(context) {
            const eventType = (context.eventType || '').toLowerCase();
            if (eventType.includes('non_institutional')) {
                return { category: 'nonInstitutional', color: CATEGORY_COLORS.nonInstitutional };
            }
            if (eventType.includes('institutional')) {
                return { category: 'institutional', color: CATEGORY_COLORS.institutional };
            }

            const serviceCategory = (context.serviceCategory || '').toLowerCase();
            if (serviceCategory.includes('non')) {
                return { category: 'nonInstitutional', color: CATEGORY_COLORS.nonInstitutional };
            }
            if (serviceCategory.includes('institutional')) {
                return { category: 'institutional', color: CATEGORY_COLORS.institutional };
            }

            return { category: 'other', color: CATEGORY_COLORS.other };
        }

        function formatLocalDate(date) {
            const d = date instanceof Date ? date : new Date(date);
            if (Number.isNaN(d.getTime())) return extractDatePart(date);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        function formatLabel(value) {
            if (!value) return '—';
            return String(value)
                .replace(/_/g, ' ')
                .replace(/\s+/g, ' ')
                .trim()
                .replace(/\b\w/g, char => char.toUpperCase());
        }

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '—';
            }
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function toPlainText(html) {
            return String(html ?? '').replace(/<[^>]+>/g, '');
        }

        function formatDateDisplay(dateValue) {
            if (!dateValue) return '—';
            const date = dateValue instanceof Date ? dateValue : new Date(dateValue);
            if (Number.isNaN(date.getTime())) {
                return escapeHtml(dateValue);
            }
            return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: 'numeric', timeZone: APP_TIMEZONE }).format(date);
        }

        function formatTimeDisplay(dateValue) {
            if (!dateValue) return '—';
            const date = dateValue instanceof Date ? dateValue : new Date(dateValue);
            if (Number.isNaN(date.getTime())) {
                return escapeHtml(dateValue);
            }
            return new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit', timeZone: APP_TIMEZONE }).format(date);
        }

        const reservationEvents = rawReservations.map(reservation => {
            const scheduleTime = reservation.schedule_time || extractTimePart(reservation.schedule_date);
            const scheduleDateTime = combineDateAndTime(reservation.schedule_date, scheduleTime);
            const fallbackStart = scheduleDateTime || reservation.schedule_date || null;

            const { category, color } = resolveCategory({ serviceCategory: reservation.service?.service_category });

            return {
                id: `res-${reservation.reservation_id}`,
                title: reservation.activity_name || reservation.service?.service_name || 'Reservation',
                start: fallbackStart,
                backgroundColor: color,
                borderColor: color,
                classNames: ['reservation-event'],
                extendedProps: {
                    entryType: 'reservation',
                    entryLabel: ENTRY_LABELS.reservation,
                    category,
                    categoryLabel: CATEGORY_LABELS[category],
                    scheduleDate: extractDatePart(reservation.schedule_date),
                    scheduleTime,
                    venue: reservation.custom_venue_name || reservation.venue?.name,
                    service: reservation.service?.service_name,
                    status: reservation.status,
                    participants: reservation.participants_count,
                    raw: reservation
                }
            };
        });

        const scheduleEvents = rawSchedules.map(schedule => {
            const start = combineDateAndTime(schedule.schedule_date, schedule.start_time) || schedule.schedule_date;
            const end = combineDateAndTime(schedule.schedule_date, schedule.end_time);
            const { category, color } = resolveCategory({
                eventType: schedule.event_type,
                serviceCategory: schedule.mass_subtype || schedule.title
            });

            return {
                id: `sched-${schedule.schedule_id}`,
                title: schedule.title || formatLabel(schedule.event_type) || 'Schedule',
                start,
                end,
                backgroundColor: color,
                borderColor: color,
                classNames: ['schedule-event'],
                extendedProps: {
                    entryType: 'schedule',
                    entryLabel: ENTRY_LABELS.schedule,
                    category,
                    categoryLabel: CATEGORY_LABELS[category],
                    status: schedule.event_type,
                    scheduleDate: extractDatePart(schedule.schedule_date),
                    scheduleTime: schedule.start_time,
                    venue: schedule.location || schedule.venue?.name,
                    public: Boolean(schedule.is_public),
                    raw: schedule
                }
            };
        });

        // Organization bookings mapped to events
        const orgBookingsNode = document.getElementById('requestor-org-bookings-json');
        const rawOrgBookings = orgBookingsNode ? JSON.parse(orgBookingsNode.textContent || '[]') : [];

        const orgBookingEvents = rawOrgBookings.map(booking => {
            const datePart = extractDatePart(booking.requested_date);
            const timePart = extractTimePart(booking.requested_date);
            const start = combineDateAndTime(datePart, timePart) || datePart;
            return {
                id: `org-${booking.id}`,
                title: booking.activity_name || (booking.organization?.org_name ? `${booking.organization.org_name} Booking` : 'Organization Booking'),
                start,
                backgroundColor: CATEGORY_COLORS.other,
                borderColor: CATEGORY_COLORS.other,
                classNames: ['organization-booking-event'],
                extendedProps: {
                    entryType: 'org_booking',
                    entryLabel: 'Organization Booking',
                    category: 'other',
                    categoryLabel: CATEGORY_LABELS.other,
                    scheduleDate: datePart,
                    scheduleTime: timePart,
                    venue: booking.requested_venue,
                    service: booking.activity_name,
                    status: booking.status,
                    participants: booking.estimated_participants,
                    organization: booking.organization?.org_name,
                    raw: booking
                }
            };
        });

        const calendar = new Calendar(calendarHost, {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
            initialView: 'dayGridMonth',
            timeZone: APP_TIMEZONE,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            height: 'auto',
            events: [...reservationEvents, ...scheduleEvents, ...orgBookingEvents],
            eventClick(info) {
                const { extendedProps } = info.event;
                const raw = extendedProps.raw || {};
                const rows = [];

                rows.push(`<strong>Entry:</strong> ${escapeHtml(extendedProps.entryLabel)}`);
                if (extendedProps.categoryLabel) {
                    rows.push(`<strong>Category:</strong> ${escapeHtml(extendedProps.categoryLabel)}`);
                }
                rows.push(`<strong>Date:</strong> ${formatDateDisplay(info.event.start || extendedProps.scheduleDate || raw.schedule_date)}`);

                if (extendedProps.entryType === 'reservation') {
                    const displayTime = extendedProps.scheduleTime || raw.schedule_time;
                    rows.push(`<strong>Time:</strong> ${displayTime ? escapeHtml(displayTime) : formatTimeDisplay(info.event.start)}`);
                    rows.push(`<strong>Service:</strong> ${escapeHtml(extendedProps.service || '—')}`);
                    
                    let statusLabel = formatLabel(extendedProps.status);
                    if (extendedProps.status === 'adviser_approved') {
                         statusLabel = (raw.priest_selection_type === 'external') ? 'Awaiting Admin' : 'Awaiting Priest';
                    }
                    rows.push(`<strong>Status:</strong> ${escapeHtml(statusLabel)}`);
                    
                    if (raw.purpose) rows.push(`<strong>Purpose:</strong> ${escapeHtml(raw.purpose)}`);
                    if (raw.theme) rows.push(`<strong>Theme:</strong> ${escapeHtml(raw.theme)}`);
                    if (raw.details) rows.push(`<strong>Details:</strong> ${escapeHtml(raw.details)}`);
                    if (raw.commentator) rows.push(`<strong>Commentator:</strong> ${escapeHtml(raw.commentator)}`);
                    if (raw.readers) rows.push(`<strong>Readers:</strong> ${escapeHtml(raw.readers)}`);
                    if (raw.psalmist) rows.push(`<strong>Psalmist:</strong> ${escapeHtml(raw.psalmist)}`);
                    if (raw.prayer_leader) rows.push(`<strong>Prayer Leader:</strong> ${escapeHtml(raw.prayer_leader)}`);
                    if (extendedProps.participants) rows.push(`<strong>Participants:</strong> ${escapeHtml(extendedProps.participants)}`);
                    if (extendedProps.venue) rows.push(`<strong>Venue:</strong> ${escapeHtml(extendedProps.venue)}`);
                    if (raw.officiant) {
                        const priestName = raw.officiant.full_name || [raw.officiant.first_name, raw.officiant.last_name].filter(Boolean).join(' ');
                        if (priestName) {
                            rows.push(`<strong>Assigned Priest:</strong> ${escapeHtml(priestName)}`);
                        }
                    } else {
                        const externalCandidates = [
                            raw.external_priest_name,
                            raw.external_officiant_name,
                            raw.external_presider_name,
                            raw.external_priest,
                            raw.officiant_external_name,
                            raw.officiant_external,
                            raw.officiant && raw.officiant.external_priest_name,
                            extendedProps.scheduleData && extendedProps.scheduleData.external_priest_name,
                            extendedProps.scheduleData && extendedProps.scheduleData.external_priest,
                            raw.schedule && raw.schedule.external_priest_name,
                            raw.schedule && raw.schedule.external_priest
                        ];
                        const externalName = externalCandidates.find(v => v && String(v).trim());
                        if (externalName) rows.push(`<strong>Presider:</strong> ${escapeHtml(externalName)} (External)`);
                    }
                } else if (extendedProps.entryType === 'org_booking') {
                    const displayTime = extendedProps.scheduleTime;
                    rows.push(`<strong>Time:</strong> ${displayTime ? escapeHtml(displayTime) : formatTimeDisplay(info.event.start)}`);
                    rows.push(`<strong>Activity:</strong> ${escapeHtml(extendedProps.service || '—')}`);
                    rows.push(`<strong>Status:</strong> ${escapeHtml(formatLabel(extendedProps.status))}`);
                    if (extendedProps.organization) rows.push(`<strong>Organization:</strong> ${escapeHtml(extendedProps.organization)}`);
                    if (extendedProps.participants) rows.push(`<strong>Participants:</strong> ${escapeHtml(extendedProps.participants)}`);
                    if (extendedProps.venue) rows.push(`<strong>Venue:</strong> ${escapeHtml(extendedProps.venue)}`);
                } else {
                    const computedStart = extendedProps.scheduleTime
                        ? combineDateAndTime(extendedProps.scheduleDate, extendedProps.scheduleTime)
                        : info.event.start;
                    const computedEnd = raw.end_time
                        ? combineDateAndTime(extendedProps.scheduleDate, raw.end_time)
                        : info.event.end;

                    rows.push(`<strong>Start Time:</strong> ${escapeHtml(formatTimeDisplay(computedStart))}`);
                    rows.push(`<strong>End Time:</strong> ${escapeHtml(formatTimeDisplay(computedEnd))}`);
                    rows.push(`<strong>Type:</strong> ${escapeHtml(formatLabel(extendedProps.status))}`);
                    if (raw.mass_subtype) {
                        rows.push(`<strong>Mass Subtype:</strong> ${escapeHtml(formatLabel(raw.mass_subtype))}`);
                    }
                    if (extendedProps.venue) rows.push(`<strong>Venue:</strong> ${escapeHtml(extendedProps.venue)}`);
                    if (raw.description) rows.push(`<strong>Description:</strong> ${escapeHtml(raw.description)}`);
                    rows.push(`<strong>Visible Publicly:</strong> ${extendedProps.public ? 'Yes' : 'No'}`);
                    if (raw.priest) {
                        const priestName = raw.priest.full_name || [raw.priest.first_name, raw.priest.last_name].filter(Boolean).join(' ');
                        if (priestName) rows.push(`<strong>Presider:</strong> ${escapeHtml(priestName)}`);
                    } else {
                        const externalCandidates = [
                            raw.external_priest_name,
                            raw.external_officiant_name,
                            raw.external_presider_name,
                            raw.external_priest,
                            raw.priest && raw.priest.external_priest_name,
                            extendedProps.scheduleData && extendedProps.scheduleData.external_priest_name,
                            extendedProps.scheduleData && extendedProps.scheduleData.external_priest,
                            raw.schedule && raw.schedule.external_priest_name
                        ];
                        const externalName = externalCandidates.find(v => v && String(v).trim());
                        if (externalName) rows.push(`<strong>Presider:</strong> ${escapeHtml(externalName)} (External)`);
                    }
                }

                if (sweetAlert && typeof sweetAlert.fire === 'function') {
                    sweetAlert.fire({
                        title: escapeHtml(info.event.title),
                        html: `<div class="text-left space-y-1 text-sm">${rows.map(row => `<p>${row}</p>`).join('')}</div>`,
                        icon: 'info',
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#10B981'
                    });
                } else {
                    const plainRows = rows.map(toPlainText);
                    const plainTitle = info.event.title || '';
                    window.alert(`${plainTitle}\n\n${plainRows.join('\n')}`);
                }
            },
            eventContent(arg) {
                const wrapper = document.createElement('div');
                wrapper.className = 'fc-event-content-enhanced';
                wrapper.style.padding = '4px 6px';
                wrapper.style.fontSize = '0.75rem';
                wrapper.style.lineHeight = '1.3';
                wrapper.style.overflow = 'hidden';
                
                const { extendedProps } = arg.event;
                const isReservation = extendedProps.entryType === 'reservation';
                
                // Title with time
                const titleDiv = document.createElement('div');
                titleDiv.style.fontWeight = '700';
                titleDiv.style.marginBottom = '2px';
                titleDiv.style.fontSize = '0.8rem';
                
                let displayTime = '';
                if (isReservation && extendedProps.scheduleTime) {
                    displayTime = extendedProps.scheduleTime.substring(0, 5); // HH:MM
                } else if (arg.timeText) {
                    displayTime = arg.timeText;
                }
                
                titleDiv.innerHTML = displayTime 
                    ? `<span style="font-weight: 800; color: rgba(255,255,255,0.95);">${escapeHtml(displayTime)}</span> ${escapeHtml(arg.event.title)}`
                    : escapeHtml(arg.event.title);
                wrapper.appendChild(titleDiv);
                
                // Service name for reservations
                if (isReservation && extendedProps.service) {
                    const serviceDiv = document.createElement('div');
                    serviceDiv.style.fontSize = '0.7rem';
                    serviceDiv.style.opacity = '0.95';
                    serviceDiv.style.marginTop = '2px';
                    serviceDiv.style.fontWeight = '500';
                    serviceDiv.innerHTML = `📋 ${escapeHtml(extendedProps.service)}`;
                    wrapper.appendChild(serviceDiv);
                }
                
                // Venue
                if (extendedProps.venue) {
                    const venueDiv = document.createElement('div');
                    venueDiv.style.fontSize = '0.7rem';
                    venueDiv.style.opacity = '0.9';
                    venueDiv.style.marginTop = '2px';
                    venueDiv.style.fontStyle = 'italic';
                    venueDiv.innerHTML = `📍 ${escapeHtml(extendedProps.venue)}`;
                    wrapper.appendChild(venueDiv);
                }
                
                // Status badge for reservations
                if (isReservation && extendedProps.status) {
                    const statusDiv = document.createElement('div');
                    statusDiv.style.fontSize = '0.65rem';
                    statusDiv.style.marginTop = '3px';
                    statusDiv.style.padding = '2px 6px';
                    statusDiv.style.borderRadius = '4px';
                    statusDiv.style.display = 'inline-block';
                    statusDiv.style.fontWeight = '600';
                    statusDiv.style.textTransform = 'uppercase';
                    statusDiv.style.letterSpacing = '0.5px';
                    
                    const status = extendedProps.status.toLowerCase();
                    if (status === 'approved') {
                        statusDiv.style.backgroundColor = 'rgba(16, 185, 129, 0.9)';
                        statusDiv.style.color = 'white';
                        statusDiv.innerHTML = '✓ Approved';
                    } else if (status === 'admin_approved') {
                        statusDiv.style.backgroundColor = 'rgba(59, 130, 246, 0.9)';
                        statusDiv.style.color = 'white';
                        statusDiv.innerHTML = '✓ Admin Approved';
                    } else if (status === 'adviser_approved') {
                        statusDiv.style.backgroundColor = 'rgba(234, 179, 8, 0.9)'; // yellow-500
                        statusDiv.style.color = 'white';
                        const label = (extendedProps.raw && extendedProps.raw.priest_selection_type === 'external') ? 'Awaiting Admin' : 'Awaiting Priest';
                        statusDiv.innerHTML = '⏳ ' + escapeHtml(label);
                    } else if (status === 'pending') {
                        statusDiv.style.backgroundColor = 'rgba(251, 191, 36, 0.9)';
                        statusDiv.style.color = 'white';
                        statusDiv.innerHTML = '⏳ Pending';
                    } else {
                        statusDiv.style.backgroundColor = 'rgba(148, 163, 184, 0.9)';
                        statusDiv.style.color = 'white';
                        statusDiv.innerHTML = escapeHtml(formatLabel(extendedProps.status));
                    }
                    
                    wrapper.appendChild(statusDiv);
                }
                
                return { domNodes: [wrapper] };
            }
        });

        calendar.render();
    }
</script>
<script id="requestor-reservations-json" type="application/json">{!! $reservations->toJson(JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
<script id="requestor-schedules-json" type="application/json">{!! ($schedules ?? collect())->toJson(JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@push('scripts')
<script type="application/json" id="requestor-org-bookings-json">@json($orgBookings ?? [])</script>
@endpush
