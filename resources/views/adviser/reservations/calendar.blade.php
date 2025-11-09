@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6 flex items-center gap-3">
        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Organization Calendar
    </h1>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div id="adviserCalendar"></div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const rawReservations = JSON.parse(document.getElementById('adviser-reservations-json').textContent || '[]');
        const rawSchedules = JSON.parse(document.getElementById('adviser-schedules-json').textContent || '[]');

        const reservationEvents = rawReservations.map(r => {
            let dateStr = r.schedule_date;
            if (typeof dateStr === 'string' && dateStr.includes(' ')) {
                dateStr = dateStr.split(' ')[0];
            }
            return {
                id: 'res-' + r.reservation_id,
                source: 'reservation',
                title: r.activity_name || r.service?.service_name || 'Reservation',
                start: dateStr + (r.schedule_time ? 'T' + r.schedule_time : ''),
                backgroundColor: '#10B981',
                borderColor: '#059669',
                extendedProps: {
                    venue: r.custom_venue_name || (r.venue ? r.venue.name : ''),
                    service: r.service?.service_name,
                    status: r.status,
                    participants: r.participants_count,
                    type: 'reservation'
                }
            };
        });

        const scheduleEvents = rawSchedules.map(s => {
            const start = s.schedule_date + (s.start_time ? 'T' + s.start_time : '');
            const end = s.schedule_date + (s.end_time ? 'T' + s.end_time : '');
            return {
                id: 'sched-' + s.schedule_id,
                source: 'schedule',
                title: s.title || (s.event_type ? s.event_type.replaceAll('_',' ') : 'Schedule'),
                start: start,
                end: s.end_time ? end : undefined,
                backgroundColor: s.is_public ? '#3B82F6' : '#1D4ED8',
                borderColor: '#2563EB',
                extendedProps: {
                    venue: s.location || (s.venue ? s.venue.name : ''),
                    status: s.event_type,
                    public: !!s.is_public,
                    type: 'schedule'
                }
            };
        });

        const events = [...reservationEvents, ...scheduleEvents];

        if (typeof window.Calendar === 'undefined') {
            return setTimeout(arguments.callee, 150);
        }

        const cal = new Calendar(document.getElementById('adviserCalendar'), {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
            events: events,
            height: 'auto',
            eventClick(info) {
                const e = info.event;
                const p = e.extendedProps;
                const venue = p.venue ? `<p class='mt-2 text-sm'><strong>Venue:</strong> ${p.venue}</p>` : '';
                const service = p.service ? `<p class='mt-1 text-sm'><strong>Service:</strong> ${p.service}</p>` : '';
                const status = p.status ? `<p class='mt-1 text-sm'><strong>Status:</strong> ${String(p.status).replaceAll('_',' ')}</p>` : '';
                const participants = p.participants ? `<p class='mt-1 text-sm'><strong>Participants:</strong> ${p.participants}</p>` : '';
                const type = p.type ? `<p class='mt-1 text-xs text-gray-500'>(${p.type})</p>` : '';
                Swal.fire({
                    title: e.title,
                    html: `<div class='text-left'>${venue}${service}${status}${participants}${type}</div>`,
                    icon: 'info',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#10B981'
                });
            },
            eventContent(arg) {
                const wrapper = document.createElement('div');
                wrapper.style.fontSize = '0.7rem';
                wrapper.style.fontWeight = '600';
                wrapper.innerHTML = `<div>${arg.event.title}</div>`;
                return { domNodes: [wrapper] };
            }
        });
        cal.render();
    });
</script>
<script id="adviser-reservations-json" type="application/json">{!! $reservations->toJson(JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
<script id="adviser-schedules-json" type="application/json">{!! ($schedules ?? collect())->toJson(JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
