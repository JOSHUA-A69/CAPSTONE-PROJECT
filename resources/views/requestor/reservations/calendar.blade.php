@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-2 sm:py-4 lg:py-6 px-2 sm:px-4 lg:px-6">
    {{-- Compact Header for Mobile --}}
    <div class="mb-2 sm:mb-4 lg:mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="p-1.5 sm:p-2 lg:p-2.5 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg shadow-md flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 dark:text-white">
                    My Calendar
                </h1>
            </div>
            {{-- Empty State Badge - Inline for Mobile --}}
            @php $hasSchedules = isset($schedules) && $schedules && $schedules->count() > 0; @endphp
            @if($reservations->isEmpty() && ! $hasSchedules)
                <span class="hidden sm:inline-flex items-center gap-1 px-2 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-medium rounded-full">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    No events
                </span>
            @endif
        </div>
    </div>

    {{-- Calendar Container --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl shadow-md sm:shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div id="requestorCalendar" class="calendar-container"></div>
    </div>
</div>
@endsection


@push('styles')
<style>
    /* Calendar container */
    .calendar-container {
        padding: 10px;
    }

    /* Enhanced calendar event styling */
    .fc-event-content-enhanced {
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .fc-event {
        border-radius: 3px !important;
        border-width: 0 !important;
        border-left: 3px solid !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        transition: all 0.15s ease;
    }

    .fc-event:hover {
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }

    .fc-daygrid-event {
        margin: 1px 2px;
        padding: 0;
    }

    /* Calendar title styling */
    .fc .fc-toolbar-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
    }

    .dark .fc .fc-toolbar-title {
        color: #f3f4f6;
    }

    /* Button styling - Clean and minimal */
    .fc .fc-button-primary {
        background-color: #5b5fc7 !important;
        border-color: #5b5fc7 !important;
        font-weight: 500;
        font-size: 0.7rem;
        padding: 6px 10px !important;
        border-radius: 6px !important;
        transition: all 0.15s ease;
        margin: 0;
        min-height: 30px;
        text-transform: capitalize;
    }

    .fc .fc-button-primary:hover {
        background-color: #4a4eb5 !important;
        border-color: #4a4eb5 !important;
    }

    .fc .fc-button-primary:disabled {
        background-color: #d1d5db !important;
        border-color: #d1d5db !important;
        color: #6b7280 !important;
        opacity: 1;
    }

    .fc .fc-button-active {
        background-color: #4338ca !important;
        border-color: #4338ca !important;
    }

    /* Toolbar layout */
    .fc .fc-toolbar {
        margin-bottom: 12px !important;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }

    .fc .fc-toolbar-chunk {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Button groups */
    .fc .fc-button-group {
        display: flex;
        gap: 0;
        border-radius: 6px;
        overflow: hidden;
    }

    .fc .fc-button-group > .fc-button {
        border-radius: 0 !important;
        margin: 0 !important;
    }

    .fc .fc-button-group > .fc-button:first-child {
        border-radius: 6px 0 0 6px !important;
    }

    .fc .fc-button-group > .fc-button:last-child {
        border-radius: 0 6px 6px 0 !important;
    }

    /* Table styling */
    .fc-theme-standard td, .fc-theme-standard th {
        border-color: #e5e7eb;
    }

    .dark .fc-theme-standard td, .dark .fc-theme-standard th {
        border-color: #374151;
    }

    /* Column headers */
    .fc .fc-col-header-cell {
        background-color: #f9fafb;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.02em;
        color: #6b7280;
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .dark .fc .fc-col-header-cell {
        background-color: #1f2937;
        color: #9ca3b8;
        border-bottom-color: #374151;
    }

    /* Day cells */
    .fc .fc-daygrid-day-top {
        padding: 4px;
        font-weight: 500;
        justify-content: center;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: #fefce8 !important;
    }

    .dark .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(234, 179, 8, 0.1) !important;
    }

    .fc .fc-daygrid-day-number {
        color: #374151;
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0;
    }

    .dark .fc .fc-daygrid-day-number {
        color: #d1d5db;
    }

    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background-color: #eab308;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.7rem;
    }

    /* Day cell frame - balanced height */
    .fc .fc-daygrid-day-frame {
        min-height: 60px;
    }

    /* List view styling */
    .fc-list {
        border: none !important;
    }

    .fc-list-day-cushion {
        background-color: #f9fafb !important;
        padding: 10px 12px !important;
        font-size: 0.8rem;
    }

    .dark .fc-list-day-cushion {
        background-color: #1f2937 !important;
    }

    .fc-list-event td {
        padding: 10px 12px !important;
        font-size: 0.85rem;
    }

    .fc-list-empty {
        background-color: #f9fafb !important;
        text-align: center;
        padding: 40px 20px !important;
    }

    .fc-list-empty-cushion {
        font-size: 0.9rem;
        color: #6b7280;
    }

    /* Time grid (Week view) */
    .fc-timegrid-slot {
        height: 2.5em !important;
    }

    .fc-timegrid-slot-label {
        font-size: 0.65rem !important;
        color: #9ca3af;
    }

    .fc-timegrid-axis {
        width: 45px !important;
    }

    /* Scrollbar styling */
    .fc-scroller {
        scrollbar-width: thin;
        scrollbar-color: #e5e7eb transparent;
    }

    .fc-scroller::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .fc-scroller::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 4px;
    }

    /* ========== MOBILE STYLES (max-width: 480px) ========== */
    @media (max-width: 480px) {
        .calendar-container {
            padding: 6px;
        }

        /* Toolbar - centered stacked layout */
        .fc .fc-toolbar {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px !important;
        }

        .fc .fc-toolbar-chunk {
            justify-content: center;
        }

        /* Title */
        .fc .fc-toolbar-title {
            font-size: 1rem !important;
            font-weight: 600;
        }

        /* Compact buttons */
        .fc .fc-button-primary {
            font-size: 0.65rem !important;
            padding: 5px 10px !important;
            min-height: 28px;
        }

        /* Column headers */
        .fc .fc-col-header-cell {
            font-size: 0.6rem;
            padding: 6px 0;
        }

        /* Day numbers */
        .fc .fc-daygrid-day-number {
            font-size: 0.75rem;
        }

        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
        }

        /* Day cell frame */
        .fc .fc-daygrid-day-frame {
            min-height: 50px;
        }

        /* Week view */
        .fc-timegrid-slot {
            height: 2em !important;
        }

        .fc-timegrid-slot-label {
            font-size: 0.55rem !important;
        }

        .fc-timegrid-axis {
            width: 35px !important;
        }

        /* List view */
        .fc-list-day-cushion {
            padding: 8px 10px !important;
            font-size: 0.75rem;
        }

        .fc-list-event td {
            padding: 8px 10px !important;
            font-size: 0.8rem;
        }
    }

    /* Tablet */
    @media (min-width: 481px) and (max-width: 768px) {
        .calendar-container {
            padding: 10px;
        }

        .fc .fc-toolbar {
            flex-direction: row;
            justify-content: space-between;
        }

        .fc .fc-toolbar-title {
            font-size: 1.1rem !important;
        }

        .fc .fc-daygrid-day-frame {
            min-height: 55px;
        }
    }

    /* Desktop */
    @media (min-width: 769px) {
        .calendar-container {
            padding: 16px;
        }

        .fc .fc-toolbar {
            flex-direction: row;
            justify-content: space-between;
            margin-bottom: 16px !important;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem !important;
        }

        .fc .fc-button-primary {
            font-size: 0.75rem;
            padding: 6px 14px !important;
            min-height: 34px;
        }

        .fc .fc-col-header-cell {
            font-size: 0.7rem;
            padding: 10px 4px;
        }

        .fc .fc-daygrid-day-frame {
            min-height: 80px;
        }

        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            width: 26px;
            height: 26px;
            font-size: 0.75rem;
        }
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
            // Auto height to show full calendar without cutting off
            height: 'auto',
            contentHeight: 'auto',
            // Mobile optimizations
            dayMaxEvents: window.innerWidth < 480 ? 2 : (window.innerWidth < 768 ? 3 : 4),
            dayMaxEventRows: window.innerWidth < 480 ? 2 : 3,
            moreLinkClick: 'popover',
            fixedWeekCount: false,
            showNonCurrentDates: true,
            // Week view settings - compact time range
            slotMinTime: '06:00:00',
            slotMaxTime: '21:00:00',
            slotDuration: '01:00:00',
            allDaySlot: false,
            expandRows: true,
            stickyHeaderDates: true,
            events: [...reservationEvents, ...scheduleEvents, ...orgBookingEvents],
            // Handle window resize
            windowResize: function(view) {
                const width = window.innerWidth;
                if (width < 480) {
                    calendar.setOption('dayMaxEvents', 2);
                } else if (width < 768) {
                    calendar.setOption('dayMaxEvents', 3);
                } else {
                    calendar.setOption('dayMaxEvents', 4);
                }
            },
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
                    const assignedPriests = [];
                    if (Array.isArray(raw.priests) && raw.priests.length > 0) {
                        raw.priests.forEach(priest => {
                            const priestName = priest.full_name || [priest.first_name, priest.middle_name, priest.last_name].filter(Boolean).join(' ');
                            if (!priestName) return;

                            const isMainCelebrant = Boolean(priest?.pivot?.is_main_celebrant)
                                || (raw.officiant_id && Number(raw.officiant_id) === Number(priest.id));

                            assignedPriests.push(isMainCelebrant ? `${priestName} (Main Celebrant)` : priestName);
                        });
                    }

                    if (assignedPriests.length > 0) {
                        rows.push(`<strong>Assigned Priest${assignedPriests.length > 1 ? 's' : ''}:</strong> ${escapeHtml(assignedPriests.join(', '))}`);
                    } else if (raw.officiant) {
                        const priestName = raw.officiant.full_name || [raw.officiant.first_name, raw.officiant.middle_name, raw.officiant.last_name].filter(Boolean).join(' ');
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

                    const organizations = [];
                    if (Array.isArray(raw.organizations) && raw.organizations.length > 0) {
                        raw.organizations.forEach(organization => {
                            const orgName = organization?.org_name || organization?.name;
                            if (orgName) {
                                organizations.push(orgName);
                            }
                        });
                    } else if (raw.organization && raw.organization.org_name) {
                        organizations.push(raw.organization.org_name);
                    }

                    if (organizations.length > 0) {
                        rows.push(`<strong>Organization${organizations.length > 1 ? 's' : ''}:</strong> ${escapeHtml(organizations.join(', '))}`);
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

                const isMobile = window.innerWidth < 480;
                const isSmallMobile = window.innerWidth < 380;

                wrapper.style.padding = isMobile ? '1px 2px' : '3px 5px';
                wrapper.style.fontSize = isMobile ? '0.6rem' : '0.72rem';
                wrapper.style.lineHeight = '1.2';
                wrapper.style.overflow = 'hidden';

                const { extendedProps } = arg.event;
                const isReservation = extendedProps.entryType === 'reservation';

                // Title with time - compact for mobile
                const titleDiv = document.createElement('div');
                titleDiv.style.fontWeight = '600';
                titleDiv.style.marginBottom = isMobile ? '0' : '1px';
                titleDiv.style.fontSize = isMobile ? '0.6rem' : '0.75rem';
                titleDiv.style.whiteSpace = 'nowrap';
                titleDiv.style.overflow = 'hidden';
                titleDiv.style.textOverflow = 'ellipsis';

                let displayTime = '';
                if (isReservation && extendedProps.scheduleTime) {
                    displayTime = extendedProps.scheduleTime.substring(0, 5);
                } else if (arg.timeText) {
                    displayTime = arg.timeText;
                }

                // On very small screens, just show time or truncated title
                if (isSmallMobile) {
                    titleDiv.innerHTML = displayTime
                        ? `<span style="font-weight: 700;">${escapeHtml(displayTime)}</span>`
                        : escapeHtml(arg.event.title.substring(0, 8));
                } else if (isMobile) {
                    titleDiv.innerHTML = displayTime
                        ? `<span style="font-weight: 700;">${escapeHtml(displayTime)}</span> ${escapeHtml(arg.event.title.substring(0, 12))}`
                        : escapeHtml(arg.event.title.substring(0, 15));
                } else {
                    titleDiv.innerHTML = displayTime
                        ? `<span style="font-weight: 700; color: rgba(255,255,255,0.95);">${escapeHtml(displayTime)}</span> ${escapeHtml(arg.event.title)}`
                        : escapeHtml(arg.event.title);
                }
                wrapper.appendChild(titleDiv);

                // Only show additional info on larger screens
                if (!isMobile) {
                    // Service name for reservations
                    if (isReservation && extendedProps.service) {
                        const serviceDiv = document.createElement('div');
                        serviceDiv.style.fontSize = '0.65rem';
                        serviceDiv.style.opacity = '0.95';
                        serviceDiv.style.marginTop = '1px';
                        serviceDiv.style.fontWeight = '500';
                        serviceDiv.style.whiteSpace = 'nowrap';
                        serviceDiv.style.overflow = 'hidden';
                        serviceDiv.style.textOverflow = 'ellipsis';
                        serviceDiv.innerHTML = `📋 ${escapeHtml(extendedProps.service)}`;
                        wrapper.appendChild(serviceDiv);
                    }

                    // Venue
                    if (extendedProps.venue) {
                        const venueDiv = document.createElement('div');
                        venueDiv.style.fontSize = '0.65rem';
                        venueDiv.style.opacity = '0.9';
                        venueDiv.style.marginTop = '1px';
                        venueDiv.style.fontStyle = 'italic';
                        venueDiv.style.whiteSpace = 'nowrap';
                        venueDiv.style.overflow = 'hidden';
                        venueDiv.style.textOverflow = 'ellipsis';
                        venueDiv.innerHTML = `📍 ${escapeHtml(extendedProps.venue)}`;
                        wrapper.appendChild(venueDiv);
                    }
                }

                // Status badge - compact version for all screens
                if (isReservation && extendedProps.status && !isSmallMobile) {
                    const statusDiv = document.createElement('div');
                    statusDiv.style.fontSize = isMobile ? '0.5rem' : '0.6rem';
                    statusDiv.style.marginTop = isMobile ? '1px' : '2px';
                    statusDiv.style.padding = isMobile ? '1px 3px' : '1px 4px';
                    statusDiv.style.borderRadius = '3px';
                    statusDiv.style.display = 'inline-block';
                    statusDiv.style.fontWeight = '600';
                    statusDiv.style.textTransform = 'uppercase';
                    statusDiv.style.letterSpacing = '0.3px';

                    const status = extendedProps.status.toLowerCase();
                    if (status === 'approved') {
                        statusDiv.style.backgroundColor = 'rgba(16, 185, 129, 0.9)';
                        statusDiv.style.color = 'white';
                        statusDiv.innerHTML = isMobile ? '✓' : '✓ Approved';
                    } else if (status === 'admin_approved') {
                        statusDiv.style.backgroundColor = 'rgba(59, 130, 246, 0.9)';
                        statusDiv.style.color = 'white';
                        statusDiv.innerHTML = isMobile ? '✓' : '✓ Admin';
                    } else if (status === 'adviser_approved') {
                        statusDiv.style.backgroundColor = 'rgba(234, 179, 8, 0.9)';
                        statusDiv.style.color = 'white';
                        const label = (extendedProps.raw && extendedProps.raw.priest_selection_type === 'external') ? 'Admin' : 'Priest';
                        statusDiv.innerHTML = isMobile ? '⏳' : '⏳ ' + escapeHtml(label);
                    } else if (status === 'pending') {
                        statusDiv.style.backgroundColor = 'rgba(251, 191, 36, 0.9)';
                        statusDiv.style.color = 'white';
                        statusDiv.innerHTML = isMobile ? '⏳' : '⏳ Pending';
                    } else {
                        statusDiv.style.backgroundColor = 'rgba(148, 163, 184, 0.9)';
                        statusDiv.style.color = 'white';
                        statusDiv.innerHTML = isMobile ? '•' : escapeHtml(formatLabel(extendedProps.status));
                    }

                    wrapper.appendChild(statusDiv);
                }

                return { domNodes: [wrapper] };
            }
        });

        calendar.render();
    }
</script>
<script id="requestor-reservations-json" type="application/json">@json($reservations)</script>
<script id="requestor-schedules-json" type="application/json">@json($schedules ?? collect())</script>
@endpush

@push('scripts')
<script type="application/json" id="requestor-org-bookings-json">@json($orgBookings ?? [])</script>
@endpush
