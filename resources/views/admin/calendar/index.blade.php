@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-3 sm:py-6 px-2 sm:px-4">
    <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold mb-3 sm:mb-4 flex items-center gap-1.5 sm:gap-2">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 lg:w-8 lg:h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span class="sm:hidden">Calendar</span>
        <span class="hidden sm:inline">Unified Calendar (Admin)</span>
    </h1>

    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-2 sm:p-4 overflow-hidden">
        <div id="adminCalendar"></div>
    </div>

    @php $hasSchedules = isset($schedules) && $schedules && $schedules->count() > 0; @endphp
    @if($reservations->isEmpty() && ! $hasSchedules)
        <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg text-yellow-800 dark:text-yellow-200 text-xs sm:text-sm font-semibold">
            No upcoming items found.
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .fc .admin-presider {
        box-shadow: 0 0 0 2px #FACC15;
        border-color: #FACC15 !important;
    }

    /* Button Styles */
    .fc .fc-button-primary {
        background-color: #6366f1 !important;
        border-color: #6366f1 !important;
        font-weight: 500;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem !important;
        border-radius: 0.375rem !important;
        transition: all 0.2s ease;
        margin: 0 0.0625rem;
        min-height: 1.75rem;
        text-transform: capitalize;
    }

    .fc .fc-button-primary:hover {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(99, 102, 241, 0.35);
    }

    .fc .fc-button-primary:disabled,
    .fc .fc-button-primary.fc-button-active {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
        opacity: 1;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.15);
    }

    /* Toolbar Styles */
    .fc .fc-toolbar {
        margin-bottom: 0.75rem !important;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .fc .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        color: #374151;
    }

    .dark .fc .fc-toolbar-title {
        color: #e5e7eb;
    }

    .dark .fc .fc-toolbar {
        border-bottom-color: #374151;
    }

    .fc .fc-toolbar-chunk {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Compact Day Grid (Month View) */
    .fc .fc-daygrid-day {
        min-height: 60px !important;
    }

    .fc .fc-daygrid-day-frame {
        min-height: 55px !important;
        padding: 2px !important;
    }

    .fc .fc-daygrid-day-top {
        padding: 2px 4px !important;
    }

    .fc .fc-daygrid-day-number {
        font-size: 0.75rem !important;
        font-weight: 500;
        padding: 2px 4px !important;
    }

    .fc .fc-daygrid-day-events {
        margin-top: 1px !important;
    }

    .fc .fc-daygrid-event {
        margin: 1px 2px !important;
        padding: 1px 3px !important;
        border-radius: 3px !important;
        font-size: 0.65rem !important;
    }

    .fc .fc-daygrid-more-link {
        font-size: 0.65rem !important;
        font-weight: 600;
        color: #6366f1;
    }

    /* Column Headers */
    .fc .fc-col-header-cell {
        padding: 6px 2px !important;
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    }

    .dark .fc .fc-col-header-cell {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    }

    .fc .fc-col-header-cell-cushion {
        font-size: 0.7rem !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: #6b7280;
        padding: 4px !important;
    }

    .dark .fc .fc-col-header-cell-cushion {
        color: #9ca3af;
    }

    /* Week View Enhancements */
    .fc-timeGridWeek-view .fc-timegrid-slot {
        height: 28px !important;
    }

    .fc-timeGridWeek-view .fc-timegrid-slot-label {
        font-size: 0.65rem !important;
        color: #9ca3af;
        vertical-align: top;
        padding-top: 2px !important;
    }

    .fc-timeGridWeek-view .fc-timegrid-col {
        min-width: 50px !important;
    }

    .fc-timeGridWeek-view .fc-timegrid-event {
        border-radius: 4px !important;
        font-size: 0.65rem !important;
        padding: 2px 4px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    .fc-timeGridWeek-view .fc-timegrid-event .fc-event-title {
        font-weight: 500;
        line-height: 1.2;
    }

    .fc-timeGridWeek-view .fc-timegrid-event .fc-event-time {
        font-size: 0.6rem !important;
        opacity: 0.85;
    }

    .fc-timeGridWeek-view .fc-timegrid-now-indicator-line {
        border-color: #ef4444 !important;
        border-width: 2px;
    }

    .fc-timeGridWeek-view .fc-timegrid-now-indicator-arrow {
        border-color: #ef4444 !important;
    }

    /* List View Compact */
    .fc-listWeek-view .fc-list-event {
        font-size: 0.75rem;
    }

    .fc-listWeek-view .fc-list-event-title {
        font-weight: 500;
    }

    .fc-listWeek-view .fc-list-day-cushion {
        padding: 4px 8px !important;
        font-size: 0.75rem;
        background: #f3f4f6;
    }

    .dark .fc-listWeek-view .fc-list-day-cushion {
        background: #1f2937;
    }

    /* Today Highlight */
    .fc .fc-day-today {
        background-color: rgba(99, 102, 241, 0.08) !important;
    }

    .dark .fc .fc-day-today {
        background-color: rgba(99, 102, 241, 0.15) !important;
    }

    /* Table Borders */
    .fc-theme-standard td,
    .fc-theme-standard th {
        border-color: #e5e7eb !important;
    }

    .dark .fc-theme-standard td,
    .dark .fc-theme-standard th {
        border-color: #374151 !important;
    }

    /* Mobile Responsive */
    @media (max-width: 640px) {
        .fc .fc-toolbar {
            flex-direction: row;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .fc .fc-toolbar-chunk {
            width: auto;
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .fc .fc-button-primary {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem !important;
            min-height: 1.5rem;
        }

        .fc .fc-toolbar-title {
            font-size: 1rem !important;
        }

        .fc .fc-daygrid-day {
            min-height: 50px !important;
        }

        .fc .fc-daygrid-day-frame {
            min-height: 45px !important;
        }

        .fc .fc-col-header-cell-cushion {
            font-size: 0.6rem !important;
        }

        .fc .fc-daygrid-day-number {
            font-size: 0.65rem !important;
        }

        .fc .fc-daygrid-event {
            font-size: 0.55rem !important;
            padding: 1px 2px !important;
        }

        .fc-timeGridWeek-view .fc-timegrid-slot {
            height: 24px !important;
        }

        .fc-timeGridWeek-view .fc-timegrid-slot-label {
            font-size: 0.55rem !important;
        }

        .fc-timeGridWeek-view .fc-timegrid-event {
            font-size: 0.55rem !important;
        }
    }

    /* Extra small screens (360px and below) */
    @media (max-width: 360px) {
        .fc .fc-toolbar {
            flex-direction: column;
            gap: 0.375rem;
            padding-bottom: 0.5rem;
            margin-bottom: 0.5rem !important;
        }

        .fc .fc-toolbar-chunk {
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .fc .fc-button-primary {
            font-size: 0.6rem !important;
            padding: 0.15rem 0.3rem !important;
            min-height: 1.25rem;
            margin: 0 0.0625rem;
        }

        .fc .fc-toolbar-title {
            font-size: 0.875rem !important;
        }

        .fc .fc-view-harness {
            overflow-x: hidden !important;
        }

        .fc .fc-scrollgrid {
            width: 100% !important;
        }

        .fc .fc-daygrid-day {
            min-height: 40px !important;
        }

        .fc .fc-daygrid-day-frame {
            min-height: 35px !important;
            padding: 1px !important;
        }

        .fc .fc-daygrid-day-top {
            padding: 1px 2px !important;
        }

        .fc .fc-col-header-cell {
            padding: 3px 1px !important;
        }

        .fc .fc-col-header-cell-cushion {
            font-size: 0.5rem !important;
            padding: 2px !important;
            letter-spacing: 0;
        }

        .fc .fc-daygrid-day-number {
            font-size: 0.55rem !important;
            padding: 1px 2px !important;
        }

        .fc .fc-daygrid-event {
            font-size: 0.45rem !important;
            padding: 0 1px !important;
            margin: 0 1px !important;
            line-height: 1.1;
        }

        .fc .fc-daygrid-more-link {
            font-size: 0.45rem !important;
        }

        .fc .fc-daygrid-day-events {
            margin-top: 0 !important;
        }

        .fc-timeGridWeek-view .fc-timegrid-col {
            min-width: 35px !important;
        }

        .fc-timeGridWeek-view .fc-timegrid-slot {
            height: 20px !important;
        }

        .fc-timeGridWeek-view .fc-timegrid-slot-label {
            font-size: 0.45rem !important;
        }

        .fc-timeGridWeek-view .fc-timegrid-event {
            font-size: 0.45rem !important;
            padding: 1px 2px !important;
        }

        .fc-listWeek-view .fc-list-event {
            font-size: 0.65rem;
        }

        .fc-listWeek-view .fc-list-day-cushion {
            padding: 3px 6px !important;
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', initializeAdminCalendar);

    function initializeAdminCalendar() {
        if (typeof window.Calendar === 'undefined') {
            setTimeout(initializeAdminCalendar, 150);
            return;
        }

        const reservationNode = document.getElementById('admin-reservations-json');
        const scheduleNode = document.getElementById('admin-schedules-json');
        const orgBookingsNode = document.getElementById('admin-org-bookings-json');
        const adminNode = document.getElementById('admin-id-json');
        const calendarHost = document.getElementById('adminCalendar');
        const sweetAlert = window.Swal;

        if (!reservationNode || !calendarHost) {
            return;
        }

        const rawReservations = JSON.parse(reservationNode.textContent || '[]');
        const rawSchedules = scheduleNode ? JSON.parse(scheduleNode.textContent || '[]') : [];
        const rawOrgBookings = orgBookingsNode ? JSON.parse(orgBookingsNode.textContent || '[]') : [];
        const adminId = adminNode ? Number(JSON.parse(adminNode.textContent || '0')) : 0;

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
            reservation: 'Reservation',
            schedule: 'Staff-Plotted Schedule'
        };

        function extractDatePart(value) {
            if (!value) return null;
            if (value instanceof Date) {
                return value.toISOString().split('T')[0];
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
            // Prefer parsing via Date to respect timezone information
            const date = value instanceof Date ? value : new Date(value);
            if (Number.isNaN(date.getTime())) {
                if (typeof value === 'string') {
                    if (value.includes('T')) {
                        const fragment = value.split('T')[1] || '';
                        return fragment.replace(/Z$/, '').slice(0, 8) || null;
                    }
                    if (value.includes(' ')) {
                        const fragment = value.split(' ')[1] || '';
                        return fragment.slice(0, 8) || null;
                    }
                }
                return null;
            }
            return date.toTimeString().slice(0, 8);
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
            return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function formatTimeDisplay(dateValue) {
            if (!dateValue) return '—';
            const date = dateValue instanceof Date ? dateValue : new Date(dateValue);
            if (Number.isNaN(date.getTime())) {
                return escapeHtml(dateValue);
            }
            return date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
        }

        function buildName(person) {
            if (!person) return '';
            if (person.full_name) return person.full_name;
            return [person.first_name, person.middle_name, person.last_name].filter(Boolean).join(' ');
        }

        const reservationEvents = rawReservations.map(reservation => {
            const scheduleTime = reservation.schedule_time || extractTimePart(reservation.schedule_date);
            const scheduleDateTime = combineDateAndTime(reservation.schedule_date, scheduleTime);
            const fallbackStart = scheduleDateTime || reservation.schedule_date || null;

            const { category, color } = resolveCategory({
                serviceCategory: reservation.service?.service_category
            });

            const adminPresides = Number(reservation.officiant_id) === adminId;

            return {
                id: `res-${reservation.reservation_id}`,
                title: reservation.activity_name || reservation.service?.service_name || 'Reservation',
                start: fallbackStart,
                backgroundColor: color,
                borderColor: adminPresides ? '#FACC15' : color,
                classNames: adminPresides ? ['reservation-event', 'admin-presider'] : ['reservation-event'],
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
                    adminPresides,
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

        // Organization bookings mapped to events (use local Date parsing to avoid timezone delays)
        const orgBookingEvents = rawOrgBookings.map(booking => {
            const startDate = new Date(booking.requested_date);
            const start = Number.isNaN(startDate.getTime()) ? (booking.requested_date || null) : startDate;
            const scheduleTime = !Number.isNaN(startDate.getTime()) ? startDate.toTimeString().slice(0, 8) : extractTimePart(booking.requested_date);
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
                    scheduleDate: Number.isNaN(startDate.getTime()) ? extractDatePart(booking.requested_date) : formatLocalDate(startDate),
                    scheduleTime: scheduleTime,
                    venue: booking.requested_venue,
                    service: booking.activity_name,
                    status: booking.status,
                    participants: booking.estimated_participants,
                    organization: booking.organization?.org_name,
                    requester: booking.requestor ? [booking.requestor.first_name, booking.requestor.last_name].filter(Boolean).join(' ') : null,
                    raw: booking
                }
            };
        });

        const calendar = new Calendar(calendarHost, {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
            initialView: 'dayGridMonth',
            timeZone: "{{ config('app.timezone') }}",
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                list: 'List'
            },
            height: 'auto',
            contentHeight: 'auto',
            aspectRatio: 1.8,
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',
            slotDuration: '00:30:00',
            nowIndicator: true,
            dayGridMonth: {
                dayMaxEvents: 2,
                dayMaxEventRows: 2,
            },
            timeGridWeek: {
                slotMinTime: '06:00:00',
                slotMaxTime: '22:00:00',
                slotDuration: '01:00:00',
                slotLabelInterval: '02:00:00',
                allDaySlot: true,
                slotLabelFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    omitZeroMinute: false,
                    hour12: true
                }
            },
            slotEventOverlap: false,
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
                    rows.push(`<strong>Time:</strong> ${displayTime ? escapeHtml(displayTime) : escapeHtml(formatTimeDisplay(info.event.start))}`);
                    rows.push(`<strong>Service:</strong> ${escapeHtml(extendedProps.service || '—')}`);
                    rows.push(`<strong>Status:</strong> ${escapeHtml(formatLabel(extendedProps.status))}`);
                    if (raw.organization?.org_name) {
                        rows.push(`<strong>Organization:</strong> ${escapeHtml(raw.organization.org_name)}`);
                    }
                    const requester = raw.requestor || raw.user;
                    const requesterName = buildName(requester);
                    if (requesterName) {
                        rows.push(`<strong>Requester:</strong> ${escapeHtml(requesterName)}`);
                    }
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
                        const officiantName = buildName(raw.officiant);
                        if (officiantName) {
                            const suffix = extendedProps.adminPresides ? ' (You)' : '';
                            rows.push(`<strong>Assigned Priest:</strong> ${escapeHtml(officiantName + suffix)}`);
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
                    rows.push(`<strong>Time:</strong> ${displayTime ? escapeHtml(displayTime) : escapeHtml(formatTimeDisplay(info.event.start))}`);
                    rows.push(`<strong>Activity:</strong> ${escapeHtml(extendedProps.service || '—')}`);
                    rows.push(`<strong>Status:</strong> ${escapeHtml(formatLabel(extendedProps.status))}`);
                    if (extendedProps.organization) rows.push(`<strong>Organization:</strong> ${escapeHtml(extendedProps.organization)}`);
                    if (extendedProps.requester) rows.push(`<strong>Requester:</strong> ${escapeHtml(extendedProps.requester)}`);
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
                    if (raw.priest) {
                        const presiderName = buildName(raw.priest);
                        if (presiderName) rows.push(`<strong>Presider:</strong> ${escapeHtml(presiderName)}`);
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
                    rows.push(`<strong>Visible Publicly:</strong> ${extendedProps.public ? 'Yes' : 'No'}`);
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
                wrapper.style.fontSize = '0.7rem';
                wrapper.style.fontWeight = '600';
                wrapper.innerHTML = `<div>${escapeHtml(arg.event.title)}</div>`;
                return { domNodes: [wrapper] };
            }
        });

        calendar.render();
    }
</script>
<script id="admin-reservations-json" type="application/json">{!! $reservations->toJson(JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
<script id="admin-schedules-json" type="application/json">{!! ($schedules ?? collect())->toJson(JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
<script id="admin-org-bookings-json" type="application/json">@json($orgBookings ?? [])</script>
<script id="admin-id-json" type="application/json">{!! json_encode($adminId) !!}</script>
@endpush
