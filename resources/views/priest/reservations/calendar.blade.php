@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6 flex items-center gap-3">
        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        My Confirmed Schedule
    </h1>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div id="priestCalendar"></div>
    </div>

    @if($reservations->isEmpty())
        <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg text-yellow-800 dark:text-yellow-200 text-sm font-semibold">
            No confirmed reservations found.
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const raw = @json($reservations);
        const events = raw.map(r => {
            const dateObj = new Date(r.schedule_date);
            // schedule_date may include time concatenated; ensure we format start properly
            let dateStr = r.schedule_date;
            if (typeof dateStr === 'string' && dateStr.includes(' ')) {
                dateStr = dateStr.split(' ')[0];
            }
            return {
                id: r.reservation_id,
                title: r.activity_name || r.service?.service_name || 'Reservation',
                start: dateStr + (r.schedule_time ? 'T' + r.schedule_time : ''),
                backgroundColor: '#10B981',
                borderColor: '#059669',
                extendedProps: {
                    venue: r.custom_venue_name || (r.venue ? r.venue.name : ''),
                    service: r.service?.service_name,
                    participants: r.participants_count,
                }
            }
        });

        if (typeof window.Calendar === 'undefined') {
            console.warn('FullCalendar modules not loaded yet; retrying...');
            return setTimeout(arguments.callee, 150);
        }

        const cal = new Calendar(document.getElementById('priestCalendar'), {
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
            events: events,
            height: 'auto',
            eventClick(info) {
                const e = info.event;
                const props = e.extendedProps;
                const venue = props.venue ? `<p class='mt-2 text-sm'><strong>Venue:</strong> ${props.venue}</p>` : '';
                const service = props.service ? `<p class='mt-1 text-sm'><strong>Service:</strong> ${props.service}</p>` : '';
                const participants = props.participants ? `<p class='mt-1 text-sm'><strong>Participants:</strong> ${props.participants}</p>` : '';
                Swal.fire({
                    title: e.title,
                    html: `<div class='text-left'>${venue}${service}${participants}</div>`,
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
@endpush
