@php
    use Carbon\Carbon;
    $month = $currentMonth ?? Carbon::now();
    $startOfMonth = $month->copy()->startOfMonth();
    $endOfMonth = $month->copy()->endOfMonth();
    $daysInMonth = (int) $endOfMonth->format('j');
    $prevMonth = $month->copy()->subMonth()->startOfMonth();
    $nextMonth = $month->copy()->addMonth()->startOfMonth();

    // Group upcoming reservations by date for faster lookup on the client
    $eventsByDate = collect();
    if (isset($upcomingReservations)) {
        $eventsByDate = $upcomingReservations
            ->map(function ($r) {
                $date = Carbon::parse($r->schedule_date);
                return [
                    'id' => $r->reservation_id,
                    'title' => $r->activity_name ?: ($r->service->service_name ?? 'Reservation'),
                    'date' => $date->format('Y-m-d'),
                    'date_human' => $date->format('M d, Y'),
                    'time' => $r->schedule_time ? Carbon::parse($r->schedule_time)->format('g:i A') : null,
                    'venue' => $r->venue->venue_name ?? ($r->custom_venue_name ?? null),
                    'organization' => $r->organization->org_name ?? null,
                    'participants' => $r->participants_count,
                    'presider' => $r->officiant->name
                        ?? trim(($r->officiant->first_name ?? '').' '.($r->officiant->last_name ?? ''))
                        ?: null,
                ];
            })
            ->groupBy('date');
    }

    // Default selected date: first date with events or today
    $defaultDate = $eventsByDate->keys()->sort()->first() ?? Carbon::now()->format('Y-m-d');
    // Days in current month that have events
    $daysWithReservations = $eventsByDate->keys()
        ->filter(fn($d) => Carbon::parse($d)->isSameMonth($month))
        ->map(fn($d) => Carbon::parse($d)->format('j'))
        ->values();
@endphp

<section id="home-calendar" class="mt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">
                Upcoming Reservations
            </h2>
            <a href="{{ route('calendar.public') }}" class="inline-flex items-center gap-2 text-emerald-700 dark:text-emerald-300 font-semibold hover:underline">
                View full calendar
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>

        <!-- Filters (visual only) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <select class="w-full rounded-xl border-2 border-emerald-200 text-emerald-800 dark:border-emerald-700 dark:bg-gray-800 dark:text-emerald-200 px-4 py-2">
                <option>All Events</option>
            </select>
            <select class="w-full rounded-xl border-2 border-emerald-200 text-emerald-800 dark:border-emerald-700 dark:bg-gray-800 dark:text-emerald-200 px-4 py-2">
                <option>All Venues</option>
            </select>
            <select class="w-full rounded-xl border-2 border-emerald-200 text-emerald-800 dark:border-emerald-700 dark:bg-gray-800 dark:text-emerald-200 px-4 py-2">
                <option>No Start Date</option>
            </select>
            <select class="w-full rounded-xl border-2 border-emerald-200 text-emerald-800 dark:border-emerald-700 dark:bg-gray-800 dark:text-emerald-200 px-4 py-2">
                <option>No End Date</option>
            </select>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Column 1: Month calendar -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-emerald-200/70 dark:border-emerald-800 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ request()->fullUrlWithQuery(['month' => $prevMonth->format('Y-m')]) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-300" aria-label="Previous month">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                        <h3 class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">
                            {{ $month->format('F Y') }}
                        </h3>
                        <a href="{{ request()->fullUrlWithQuery(['month' => $nextMonth->format('Y-m')]) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-300" aria-label="Next month">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="text-sm text-emerald-700/90 dark:text-emerald-400 font-semibold">Mini calendar</div>
                </div>

                <div class="grid grid-cols-7 gap-2 text-center text-xs font-semibold text-emerald-700 dark:text-emerald-300 mb-2">
                    <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                </div>

                @php
                    // Build the grid (Sunday start)
                    $firstDayIndex = (int) $startOfMonth->copy()->dayOfWeek; // 0 (Sun) - 6 (Sat)
                    $cells = [];
                    for ($i = 0; $i < $firstDayIndex; $i++) { $cells[] = null; }
                    for ($d = 1; $d <= $daysInMonth; $d++) { $cells[] = $d; }
                @endphp

                <div class="grid grid-cols-7 gap-2">
                    @foreach($cells as $cell)
                        @if(is_null($cell))
                            <div class="h-12 rounded-xl bg-gray-50 dark:bg-gray-900/30"></div>
                        @else
                            @php
                                $dateStr = $month->copy()->day($cell)->format('Y-m-d');
                                $isActive = $daysWithReservations->contains((string)$cell);
                                $isSelected = $dateStr === $defaultDate;
                            @endphp
                            <button type="button" data-date="{{ $dateStr }}"
                                class="home-cal-day h-12 w-full flex items-center justify-center rounded-xl border text-sm font-semibold transition-colors hover:border-emerald-500
                                {{ $isSelected ? 'bg-emerald-600 text-white border-emerald-600 ring-2 ring-emerald-300' : ($isActive ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700') }}">
                                {{ $cell }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Column 2: Events for selected date -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-emerald-200/70 dark:border-emerald-800 p-6 shadow-sm h-[28rem] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="homeEventsTitle" class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">Events on {{ \Carbon\Carbon::parse($defaultDate)->format('n/j/Y') }}</h3>
                    <span id="homeEventsCount" class="text-sm px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">0 events</span>
                </div>
                <div id="homeEventsForDate" class="space-y-3"></div>
                <template id="homeEventItemTpl">
                    <button type="button" class="w-full text-left p-5 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-emerald-400 hover:shadow-sm transition-colors group bg-white dark:bg-gray-800">
                        <div class="flex items-start gap-4">
                            <span class="inline-flex items-center justify-center min-w-10 h-10 rounded-full bg-emerald-50 text-emerald-700 font-extrabold text-lg ev-day">19</span>
                            <div class="flex-1">
                                <div class="text-lg font-extrabold text-gray-800 dark:text-gray-200 group-hover:text-emerald-700 ev-title">Title</div>
                                <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    <span class="inline-flex items-center gap-1 ev-date">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>M d, Y</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 ev-time hidden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>2:00 PM</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 ev-venue hidden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        <span>Venue</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 ev-presider hidden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Presider</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </button>
                </template>
            </div>

            <!-- Column 3: Event details -->
            <div>
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-emerald-200/70 dark:border-emerald-800 p-6 shadow-sm h-[28rem] overflow-y-auto" id="homeCalDetails">
                    <h4 class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300 mb-3">Event Details</h4>
                    <p class="text-gray-500 dark:text-gray-400">Select an event to see details.</p>
                </div>
            </div>
        </div>
    </div>

    <script id="homeEventsData" type="application/json">{!! json_encode($eventsByDate) !!}</script>
    <script id="homeDefaultDate" type="application/json">{!! json_encode($defaultDate) !!}</script>
    <script>
        (function(){
            const eventsByDate = JSON.parse(document.getElementById('homeEventsData').textContent || '{}');
            let selectedDate = JSON.parse(document.getElementById('homeDefaultDate').textContent || 'null') || new Date().toISOString().slice(0,10);

            function formatDateForTitle(isoDate) {
                try { return new Date(isoDate).toLocaleDateString('en-US'); } catch { return isoDate; }
            }

            function renderEvents(date) {
                const container = document.getElementById('homeEventsForDate');
                const title = document.getElementById('homeEventsTitle');
                const count = document.getElementById('homeEventsCount');
                title.textContent = `Events on ${formatDateForTitle(date)}`;
                container.innerHTML = '';
                const list = eventsByDate[date] || [];
                count.textContent = `${list.length} ${list.length === 1 ? 'event' : 'events'}`;
                if (!list.length) {
                    container.innerHTML = '<p class="text-gray-500 dark:text-gray-400">No events on this date.</p>';
                    document.getElementById('homeCalDetails').innerHTML = '<h4 class="text-xl font-extrabold text-emerald-700 dark:text-emerald-300 mb-2">Event Details</h4><p class="text-gray-500 dark:text-gray-400">Select an event to see details.</p>';
                    return;
                }
                const tpl = document.getElementById('homeEventItemTpl');
                list.forEach((ev, idx) => {
                    const node = tpl.content.cloneNode(true);
                    node.querySelector('.ev-day').textContent = (new Date(ev.date)).toLocaleDateString('en-PH', { day: '2-digit' });
                    node.querySelector('.ev-title').textContent = ev.title || 'Reservation';
                    node.querySelector('.ev-date span').textContent = ev.date_human;
                    if (ev.time) { const t = node.querySelector('.ev-time'); t.classList.remove('hidden'); t.querySelector('span').textContent = ev.time; }
                    if (ev.venue) { const v = node.querySelector('.ev-venue'); v.classList.remove('hidden'); v.querySelector('span').textContent = ev.venue; }
                    if (ev.presider) { const p = node.querySelector('.ev-presider'); p.classList.remove('hidden'); p.querySelector('span').textContent = ev.presider; }
                    const btn = node.querySelector('button');
                    btn.addEventListener('click', () => selectEvent(ev));
                    container.appendChild(node);
                    if (idx === 0) { selectEvent(ev); }
                });
            }

            function selectEvent(ev) {
                const details = document.getElementById('homeCalDetails');
                details.innerHTML = `
                    <h4 class="text-xl font-extrabold text-emerald-700 dark:text-emerald-300 mb-2">${ev.title}</h4>
                    <div class="space-y-3">
                        <div class=\"flex items-center gap-2 text-gray-700 dark:text-gray-300\">
                            <svg class=\"w-5 h-5 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z\"/></svg>
                            <span>${ev.date_human}</span>
                        </div>
                        ${ev.time ? `<div class=\"flex items-center gap-2 text-gray-700 dark:text-gray-300\"><svg class=\"w-5 h-5 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\"/></svg><span>${ev.time}</span></div>` : ''}
                        ${ev.venue ? `<div class=\"flex items-center gap-2 text-gray-700 dark:text-gray-300\"><svg class=\"w-5 h-5 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z\"/></svg><span>${ev.venue}</span></div>` : ''}
                        ${ev.organization ? `<div class=\"flex items-center gap-2 text-gray-700 dark:text-gray-300\"><svg class=\"w-5 h-5 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z\"/></svg><span>${ev.organization}</span></div>` : ''}
                        ${ev.presider ? `<div class=\"flex items-center gap-2 text-gray-700 dark:text-gray-300\"><svg class=\"w-5 h-5 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\"/></svg><span>Presider: ${ev.presider}</span></div>` : ''}
                        ${Number.isFinite(ev.participants) ? `<div class=\"flex items-center gap-2 text-gray-700 dark:text-gray-300\"><svg class=\"w-5 h-5 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0\"/></svg><span>${ev.participants} max participants</span></div>` : ''}
                    </div>
                `;
            }

            function bindDayClicks() {
                document.querySelectorAll('.home-cal-day').forEach(btn => {
                    btn.addEventListener('click', () => {
                        selectedDate = btn.getAttribute('data-date');
                        document.querySelectorAll('.home-cal-day').forEach(b => b.classList.remove('bg-emerald-600','text-white','border-emerald-600'));
                        btn.classList.add('bg-emerald-600','text-white','border-emerald-600');
                        renderEvents(selectedDate);
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', function(){
                bindDayClicks();
                renderEvents(selectedDate);
            });
        })();
    </script>
</section>
