@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6 flex items-center gap-3">
        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        My Calendar
    </h1>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div id="requestorCalendar"></div>
    </div>

    @php $hasSchedules = isset($schedules) && $schedules && $schedules->count() > 0; @endphp
    @if($reservations->isEmpty() && ! $hasSchedules)
        <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg text-yellow-800 dark:text-yellow-200 text-sm font-semibold">
            No upcoming items found.
        </div>
    @endif
</div>
@endsection


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

        const calendar = new Calendar(calendarHost, {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            height: 'auto',
            events: [...reservationEvents, ...scheduleEvents],
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
                    rows.push(`<strong>Status:</strong> ${escapeHtml(formatLabel(extendedProps.status))}`);
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
                wrapper.style.fontSize = '0.7rem';
                wrapper.style.fontWeight = '600';
                wrapper.innerHTML = `<div>${escapeHtml(arg.event.title)}</div>`;
                return { domNodes: [wrapper] };
            }
        });

        calendar.render();
    }
</script>
<script id="requestor-reservations-json" type="application/json">{!! $reservations->toJson(JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
<script id="requestor-schedules-json" type="application/json">{!! ($schedules ?? collect())->toJson(JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
