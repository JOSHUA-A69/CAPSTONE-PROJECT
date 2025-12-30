@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6 flex items-center gap-3">
        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Unified Calendar (Admin)
    </h1>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div id="adminCalendar"></div>
    </div>

    @php $hasSchedules = isset($schedules) && $schedules && $schedules->count() > 0; @endphp
    @if($reservations->isEmpty() && ! $hasSchedules)
        <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg text-yellow-800 dark:text-yellow-200 text-sm font-semibold">
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
