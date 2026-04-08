@php
    // Data is now passed from WelcomeController with caching for performance
    // $allSchedules, $upcomingReservations, $services, $venues are available globally
@endphp

<section id="home-calendar" class="py-8 sm:py-20 scroll-mt-28">
    <div class="container mx-auto px-3 sm:px-4 max-w-7xl">

        <div class="text-center mb-6 sm:mb-12">
            <div class="inline-flex items-center gap-2 sm:gap-3 px-4 sm:px-8 py-2 sm:py-4 bg-gradient-to-r from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 backdrop-blur-sm rounded-full text-emerald-700 dark:text-emerald-300 mb-4 sm:mb-6 shadow-lg border-2 border-emerald-300 dark:border-emerald-600">
                <svg class="w-4 h-4 sm:w-6 sm:h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-black text-sm sm:text-lg tracking-wide">Public Calendar</span>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="mb-4 sm:mb-6 max-w-5xl mx-auto">
            <!-- Reservation Filters (always visible) -->
            <div id="reservationFilters" class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4">
                <!-- Service Filter -->
                <div class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg p-3 sm:p-6 border-2 border-emerald-200 dark:border-emerald-700">
                    <label for="resServiceFilter" class="text-xs sm:text-sm font-black text-gray-700 dark:text-gray-300 mb-2 sm:mb-3 flex items-center gap-1 sm:gap-2">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="hidden sm:inline">Service</span>
                        <span class="sm:hidden">Service</span>
                    </label>
                    <select id="resServiceFilter" class="w-full px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-base border-2 border-gray-300 dark:border-gray-600 rounded-lg sm:rounded-xl shadow-sm focus:ring-4 focus:ring-emerald-300 focus:border-emerald-500 dark:bg-gray-700 dark:text-white font-semibold transition-all">
                        <option value="">All Services</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->service_id }}">{{ $svc->service_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Venue Filter -->
                <div class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg p-3 sm:p-6 border-2 border-green-200 dark:border-green-700">
                    <label for="resVenueFilter" class="text-xs sm:text-sm font-black text-gray-700 dark:text-gray-300 mb-2 sm:mb-3 flex items-center gap-1 sm:gap-2">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="hidden sm:inline">Venue</span>
                        <span class="sm:hidden">Venue</span>
                    </label>
                    <select id="resVenueFilter" class="w-full px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-base border-2 border-gray-300 dark:border-gray-600 rounded-lg sm:rounded-xl shadow-sm focus:ring-4 focus:ring-green-300 focus:border-green-500 dark:bg-gray-700 dark:text-white font-semibold transition-all">
                        <option value="">All Venues</option>
                        @foreach($venues as $v)
                            <option value="{{ $v->venue_id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-span-2 sm:col-span-1 bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg p-3 sm:p-6 border-2 border-teal-200 dark:border-teal-700">
                    <label for="resStatusFilter" class="text-xs sm:text-sm font-black text-gray-700 dark:text-gray-300 mb-2 sm:mb-3 flex items-center gap-1 sm:gap-2">
                        <svg class="w-3 h-3 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Status</span>
                    </label>
                    <select id="resStatusFilter" class="w-full px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-base border-2 border-gray-300 dark:border-gray-600 rounded-lg sm:rounded-xl shadow-sm focus:ring-4 focus:ring-teal-300 focus:border-teal-500 dark:bg-gray-700 dark:text-white font-semibold transition-all">
                        <option value="">All Statuses</option>
                        <option value="admin_approved">🛡️ Admin Approved</option>
                        <option value="approved">✅ Approved</option>
                        <option value="confirmed">📌 Confirmed</option>
                    </select>
                </div>
                </div>
            </div>
        </div>

        <!-- Calendar + Right Details Panel -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="lg:col-span-2 bg-gradient-to-br from-emerald-50 via-green-50 to-lime-50 dark:from-gray-900 dark:via-emerald-900 dark:to-green-900 rounded-2xl sm:rounded-3xl p-3 sm:p-6 lg:p-10 shadow-2xl border-2 border-emerald-100 dark:border-emerald-800">
                <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl overflow-hidden">
                    <div id="homepagecalendar" class="home-calendar-compact"></div>
                </div>
            </div>
            <aside id="homeEventPanel" class="hidden lg:block bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900/40 dark:to-green-900/40 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-2xl border-2 border-emerald-200 dark:border-emerald-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg sm:text-2xl font-black text-emerald-800 dark:text-emerald-300 flex items-center gap-2 sm:gap-3">
                        <svg class="w-5 h-5 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Event Details
                    </h3>
                    <button id="homeEventPanelClose" class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/60 dark:bg-gray-800/60 text-emerald-800 dark:text-emerald-200 hover:bg-white dark:hover:bg-gray-800" aria-label="Close details" title="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="homeEventDetailsBox" class="hidden mb-6"></div>
                <div class="border-t border-emerald-200 dark:border-emerald-700 pt-4">
                    <h4 class="text-sm font-black text-emerald-700 dark:text-emerald-300 mb-3">Upcoming Events</h4>
                    <div id="homepageUpcomingList" class="space-y-3">
                        <div class="text-gray-600 dark:text-gray-400 text-sm">Loading upcoming events…</div>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Info Cards - Compact on Mobile -->
        <div class="mt-4 sm:mt-8 grid grid-cols-2 gap-3 sm:gap-6">
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/40 dark:to-green-900/40 rounded-xl sm:rounded-2xl p-3 sm:p-6 shadow-lg border-2 border-emerald-200 dark:border-emerald-700">
                <h3 class="text-sm sm:text-2xl font-black text-emerald-800 dark:text-emerald-300 mb-2 sm:mb-4 flex items-center gap-1 sm:gap-3">
                    <svg class="w-4 h-4 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="hidden sm:inline">Calendar Info</span>
                    <span class="sm:hidden">Info</span>
                </h3>
                <div class="space-y-1 sm:space-y-2 text-gray-700 dark:text-gray-300">
                    <p class="flex items-start gap-1 sm:gap-2 text-xs sm:text-sm font-semibold">
                        <span class="text-emerald-600 dark:text-emerald-400">•</span>
                        <span>Click events for details</span>
                    </p>
                    <p class="flex items-start gap-1 sm:gap-2 text-xs sm:text-sm font-semibold">
                        <span class="text-emerald-600 dark:text-emerald-400">•</span>
                        <span>Filter by service, venue, or status</span>
                    </p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-teal-50 to-emerald-50 dark:from-teal-900/40 dark:to-emerald-900/40 rounded-xl sm:rounded-2xl p-3 sm:p-6 shadow-lg border-2 border-teal-200 dark:border-teal-700">
                <h3 class="text-sm sm:text-2xl font-black text-teal-800 dark:text-teal-300 mb-2 sm:mb-4 flex items-center gap-1 sm:gap-3">
                    <svg class="w-4 h-4 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Reservation Count</span>
                    <span class="sm:hidden">Count</span>
                </h3>
                <p class="text-xl sm:text-3xl font-black text-emerald-700 dark:text-emerald-300"><span id="totalEvents">0</span> events</p>
            </div>
        </div>

    </div>
</section>

<!-- Reservation Details Modal -->
<div id="reservationModal" class="hidden fixed inset-0 z-50 overflow-hidden" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="flex items-center justify-center h-screen px-4">
        <div id="reservationModalContent" class="relative w-full max-w-md sm:max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all overflow-hidden max-h-[90vh]">
            <!-- Close button - always on top -->
            <div class="absolute top-5 right-5 z-[100] pointer-events-auto">
                <button onclick="closeReservationModal()" class="w-10 h-10 flex items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/40 hover:bg-emerald-100 dark:hover:bg-emerald-800 shadow-md transition-all duration-200 transform hover:scale-110 border-2 border-emerald-200 dark:border-emerald-600" title="Close modal">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal body - populated by JavaScript -->
            <div id="reservationModalBody"></div>
        </div>
    </div>
</div>

<!-- JSON schedule data for homepage calendar -->
<script id="homeCalendarDataJson" type="application/json">@json($allSchedules)</script>
<script id="homeReservationDataJson" type="application/json">@json($upcomingReservations)</script>

<script>
let homepageCalendar;
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Blade -> JS data bridge (avoid mentioning the Blade json directive here to prevent parsing)
var __homeJsonEl = document.getElementById('homeCalendarDataJson');
var allSchedulesData = [];
try {
    allSchedulesData = JSON.parse((__homeJsonEl && __homeJsonEl.textContent) ? __homeJsonEl.textContent : '[]');
} catch (e) {
    console.error('Failed to parse home calendar JSON:', e);
    allSchedulesData = [];
}

var __resJsonEl = document.getElementById('homeReservationDataJson');
var allReservationsData = [];
try {
    allReservationsData = JSON.parse((__resJsonEl && __resJsonEl.textContent) ? __resJsonEl.textContent : '[]');
} catch (e) {
    console.error('Failed to parse reservations JSON:', e);
    allReservationsData = [];
}

// Track active tab: reservations only
var activeCalendarTab = 'reservations';

function buildHomepageCalendarEvents(reservations, schedules) {
    var reservationEvents = (reservations || []).map(function(res) {
        var dateOnly = (res.schedule_date || '').toString().slice(0, 10);
        if (dateOnly.includes('T')) dateOnly = dateOnly.split('T')[0];
        if (dateOnly.includes(' ')) dateOnly = dateOnly.split(' ')[0];

        var timeStr = '';
        var rawDate = (res.schedule_date || '').toString();
        if (rawDate.includes('T')) {
            timeStr = rawDate.split('T')[1] || '';
        } else if (rawDate.includes(' ')) {
            timeStr = rawDate.split(' ')[1] || '';
        }
        if (timeStr.includes('.')) timeStr = timeStr.split('.')[0];
        if (!timeStr || timeStr === '00:00:00') timeStr = '08:00:00';

        var eventStart = dateOnly + 'T' + timeStr;
        var color = getReservationStatusColor(res.status);
        var serviceName = (res.service && res.service.service_name) ? res.service.service_name : 'Service';
        var venueName = (res.venue && res.venue.name) ? res.venue.name : (res.custom_venue_name || '');
        var orgName = (res.organization && res.organization.org_name) ? res.organization.org_name : '';
        var privateReservation = isPrivateReservation(res);
        var publicTitle = (res.public_title || '').trim() || (privateReservation ? 'Occupied' : (res.activity_name || serviceName));

        return {
            id: 'res-' + res.reservation_id,
            title: publicTitle,
            start: eventStart,
            backgroundColor: color,
            borderColor: color,
            extendedProps: {
                type: 'reservation',
                isPrivateReservation: privateReservation,
                status: res.status,
                serviceName: serviceName,
                venueName: venueName,
                orgName: orgName,
                purpose: res.purpose || '',
                participants: res.participants_count || '',
                reservationData: res
            }
        };
    });

    var scheduleEvents = (schedules || []).map(function(schedule) {
        var dateOnly = (schedule.schedule_date || '').toString().slice(0, 10);
        if (dateOnly.includes('T')) dateOnly = dateOnly.split('T')[0];
        if (dateOnly.includes(' ')) dateOnly = dateOnly.split(' ')[0];

        var startTime = (schedule.start_time || '08:00:00').toString();
        if (startTime.includes(' ')) startTime = startTime.split(' ').pop();
        if (startTime.includes('.')) startTime = startTime.split('.')[0];
        if (startTime.length === 5) startTime = startTime + ':00';

        return {
            id: 'sched-' + schedule.schedule_id,
            title: schedule.title || 'Schedule',
            start: dateOnly + 'T' + startTime,
            backgroundColor: getEventColor(schedule.event_type),
            borderColor: getEventColor(schedule.event_type),
            extendedProps: {
                type: 'schedule',
                eventType: schedule.event_type,
                location: schedule.location,
                scheduleData: schedule
            }
        };
    });

    return reservationEvents.concat(scheduleEvents);
}

// Wait for both DOM and modules to be ready
function initWhenReady() {
    const calendarEl = document.getElementById('homepagecalendar');
    if (!calendarEl) {
        console.warn('Homepage calendar element not found');
        return;
    }

    console.log('🏠 Homepage calendar: Loading', allReservationsData.length, 'reservations and', allSchedulesData.length, 'public schedules');

    if (typeof window.Calendar === 'undefined' ||
        typeof window.dayGridPlugin === 'undefined' ||
        typeof window.timeGridPlugin === 'undefined' ||
        typeof window.listPlugin === 'undefined') {
        console.log('⏳ Waiting for FullCalendar modules to load...');
        setTimeout(initWhenReady, 100);
        return;
    }

    console.log('✅ FullCalendar modules loaded, initializing calendar...');

    // Initialize calendar with reservations + public schedules
    initializeReservationCalendar(allReservationsData, allSchedulesData);
    renderReservationUpcomingList(allReservationsData, allSchedulesData);

    // Update initial event count
    document.getElementById('totalEvents').textContent = allReservationsData.length + allSchedulesData.length;

    // Setup filter listeners for reservations only
    document.getElementById('resServiceFilter').addEventListener('change', applyReservationFilters);
    document.getElementById('resVenueFilter').addEventListener('change', applyReservationFilters);
    document.getElementById('resStatusFilter').addEventListener('change', applyReservationFilters);

    console.log('✅ Calendar initialized successfully');
}

// Start initialization when DOM is ready
document.addEventListener('DOMContentLoaded', initWhenReady);

// Handle window resize for responsive calendar
let resizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {
        if (homepageCalendar) {
            var currentFilters = {
                service: document.getElementById('resServiceFilter').value,
                venue: document.getElementById('resVenueFilter').value,
                status: document.getElementById('resStatusFilter').value
            };

            // Only refresh if crossing mobile/desktop breakpoint
            var wasMobile = homepageCalendar.getOption('dayMaxEvents') !== false;
            var isMobile = window.innerWidth <= 768;

            if (wasMobile !== isMobile) {
                console.log('📱 Screen size changed, refreshing calendar...');
                applyReservationFilters();
            }
        }
    }, 250);
});

// Handle category change to show/hide specific mass type options
function handleCategoryChange() {
    const category = document.getElementById('massCategoryFilter').value;
    const massTypeFilter = document.getElementById('massTypeFilter');
    const institutionalOptions = document.getElementById('institutionalOptions');
    const nonInstitutionalOptions = document.getElementById('nonInstitutionalOptions');

    // Reset mass type filter
    massTypeFilter.value = '';

    // Hide all optgroups first
    institutionalOptions.style.display = 'none';
    nonInstitutionalOptions.style.display = 'none';

    // Show relevant options based on category
    if (category === 'institutional_mass') {
        institutionalOptions.style.display = 'block';
        massTypeFilter.disabled = false;
    } else if (category === 'non_institutional_mass') {
        nonInstitutionalOptions.style.display = 'block';
        massTypeFilter.disabled = false;
    } else {
        // All categories - hide mass type filter
        massTypeFilter.disabled = true;
    }
}

function initializeHomeCalendar(schedules) {
    const calendarEl = document.getElementById('homepagecalendar');

    console.log('Raw schedules data:', schedules);

    const events = schedules.map(schedule => {
        console.log('Processing schedule:', schedule.title, schedule.schedule_date, schedule.start_time);
        console.log('Event type for', schedule.title, ':', schedule.event_type);
        const eventColor = getEventColor(schedule.event_type);
        console.log('Color assigned:', eventColor);

        // Extract ONLY the date part (YYYY-MM-DD) - split on space or 'T'
        let dateOnly = schedule.schedule_date;
        if (dateOnly.includes('T')) {
            dateOnly = dateOnly.split('T')[0];
        } else if (dateOnly.includes(' ')) {
            dateOnly = dateOnly.split(' ')[0];
        }

        // Extract ONLY the time part (HH:MM or HH:MM:SS)
        let startTime = schedule.start_time;
        if (startTime.includes(' ')) {
            // If there's a space, take the last part (the actual time)
            startTime = startTime.split(' ').pop();
        }
        // Remove microseconds if present (e.g., "19:54:00.000000")
        if (startTime.includes('.')) {
            startTime = startTime.split('.')[0];
        }

        let endTime = schedule.end_time;
        if (endTime) {
            if (endTime.includes(' ')) {
                endTime = endTime.split(' ').pop();
            }
            if (endTime.includes('.')) {
                endTime = endTime.split('.')[0];
            }
        } else {
            // If no end time, calculate one hour after start time
            const [hours, minutes] = startTime.split(':');
            let endHours = parseInt(hours) + 1;
            if (endHours >= 24) endHours -= 24;
            endTime = `${endHours.toString().padStart(2, '0')}:${minutes}:00`;
        }

        const eventStart = dateOnly + 'T' + startTime;
        const eventEnd = dateOnly + 'T' + endTime;

        console.log('Event datetime:', eventStart, eventEnd);

        return {
            id: schedule.schedule_id,
            title: schedule.title,
            start: eventStart,
            end: eventEnd, // Set end time to display time range
            backgroundColor: eventColor,
            borderColor: eventColor,
            extendedProps: {
                location: schedule.location,
                venue: schedule.venue, // Include venue relationship
                eventType: schedule.event_type,
                priest: schedule.priest,
                description: schedule.description,
                isPublic: schedule.is_public,
                scheduleData: schedule // Full schedule data
            }
        };
    });

    console.log('Homepage calendar: Mapped', events.length, 'events');
    console.log('Sample event:', events[0]);

    homepageCalendar = new window.Calendar(document.getElementById('homepagecalendar'), {
        plugins: [window.dayGridPlugin, window.timeGridPlugin, window.listPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: '',
            right: 'title' // Move title to the right side
        },
        buttonText: {
            today: 'Today'
        },
        events: events,
        editable: false,
        selectable: false,
        dayMaxEvents: false, // Disable max events to prevent repositioning
        eventOrder: 'start', // Order events by start time only
        height: 'auto',
        contentHeight: 650,
        fixedWeekCount: false, // Don't force 6 weeks to be shown
        showNonCurrentDates: false, // Hide dates from other months
        displayEventTime: true,
        displayEventEnd: true, // Show end time
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        eventClick: function(info) {
            renderHomeEventDetails(info.event.extendedProps.scheduleData);
        },
        eventContent: function(arg) {
            let wrapper = document.createElement('div');
            wrapper.classList.add('fc-event-main-custom');

            // Apply event colors inline to ensure they display
            const evBg = arg.event.backgroundColor || getEventColor((arg.event.extendedProps && arg.event.extendedProps.eventType) ? arg.event.extendedProps.eventType : undefined);
            const evBorder = arg.event.borderColor || evBg;

            wrapper.style.padding = '8px 10px';
            wrapper.style.fontSize = '0.7rem';
            wrapper.style.lineHeight = '1.4';
            wrapper.style.cursor = 'pointer';
            wrapper.style.backgroundColor = evBg;
            wrapper.style.border = `1px solid ${evBorder}`;
            wrapper.style.borderRadius = '10px';
            wrapper.style.color = '#ffffff';
            wrapper.style.display = 'flex';
            wrapper.style.flexDirection = 'column';
            wrapper.style.gap = '4px';
            wrapper.style.minHeight = '95px';
            wrapper.style.justifyContent = 'flex-start';

            // Format time range with clock icon
            let timeStr = arg.timeText || '';
            // If no end time, ensure we still show the start time properly formatted
            if (!timeStr && arg.event.start) {
                const startDate = new Date(arg.event.start);
                const hours = startDate.getHours();
                const minutes = startDate.getMinutes();
                const ampm = hours >= 12 ? 'pm' : 'am';
                const displayHours = hours % 12 || 12;
                timeStr = `${displayHours}:${minutes.toString().padStart(2, '0')}${ampm}`;
            }
            if (timeStr) {
                let timeDiv = document.createElement('div');
                timeDiv.style.fontSize = '0.7rem'; // Increased from 0.65rem
                timeDiv.style.fontWeight = '600'; // Increased from 500
                timeDiv.style.opacity = '0.98';
                timeDiv.style.letterSpacing = '0.01em';
                timeDiv.style.display = 'flex';
                timeDiv.style.alignItems = 'center';
                timeDiv.style.gap = '4px';
                timeDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg><span>${escapeHtml(timeStr)}</span>`;
                wrapper.appendChild(timeDiv);
            }

            // Event title with service icon
            let titleDiv = document.createElement('div');
            titleDiv.style.fontSize = '0.78rem'; // Increased from 0.72rem
            titleDiv.style.fontWeight = '700'; // Increased from 600
            titleDiv.style.lineHeight = '1.2';
            titleDiv.style.display = 'flex';
            titleDiv.style.alignItems = 'flex-start';
            titleDiv.style.gap = '4px';
            titleDiv.style.wordBreak = 'break-word';
            titleDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0; margin-top: 2px;" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg><span>${escapeHtml(arg.event.title)}</span>`;
            wrapper.appendChild(titleDiv);

            // Get venue name from relationship, fallback to location field
            const venueName = (arg.event.extendedProps && arg.event.extendedProps.scheduleData && arg.event.extendedProps.scheduleData.venue && arg.event.extendedProps.scheduleData.venue.name)
                || (arg.event.extendedProps && arg.event.extendedProps.venue && arg.event.extendedProps.venue.name);
            const locationText = venueName
                || (arg.event.extendedProps && arg.event.extendedProps.scheduleData ? arg.event.extendedProps.scheduleData.location : null)
                || (arg.event.extendedProps ? arg.event.extendedProps.location : '')
                || '';

            // Add location with icon if available
            if (locationText && locationText.trim() !== '') {
                let locationDiv = document.createElement('div');
                locationDiv.style.fontSize = '0.73rem'; // Increased from 0.68rem
                locationDiv.style.opacity = '0.95';
                locationDiv.style.fontWeight = '500'; // Increased from 400
                locationDiv.style.lineHeight = '1.2';
                locationDiv.style.display = 'flex';
                locationDiv.style.alignItems = 'flex-start';
                locationDiv.style.gap = '4px';
                locationDiv.style.wordBreak = 'break-word';
                locationDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0; margin-top: 1px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>${escapeHtml(locationText)}</span>`;
                wrapper.appendChild(locationDiv);
            }

            // Add presider with icon if available (internal or external)
            {
                const internalPriestName = (arg.event.extendedProps && arg.event.extendedProps.scheduleData && arg.event.extendedProps.scheduleData.priest && arg.event.extendedProps.scheduleData.priest.name)
                    || (arg.event.extendedProps && arg.event.extendedProps.priest && arg.event.extendedProps.priest.name);
                const externalPriestName = (arg.event.extendedProps && arg.event.extendedProps.scheduleData) ? arg.event.extendedProps.scheduleData.external_priest_name : undefined;
                const presiderName = internalPriestName || externalPriestName;
                const isExternal = !internalPriestName && !!externalPriestName;
                if (presiderName) {
                    let priestDiv = document.createElement('div');
                    priestDiv.style.fontSize = '0.73rem'; // Increased from 0.68rem
                    priestDiv.style.opacity = '0.95';
                    priestDiv.style.fontWeight = '600'; // Increased from 500
                    priestDiv.style.lineHeight = '1.3';
                    priestDiv.style.display = 'flex';
                    priestDiv.style.alignItems = 'center';
                    priestDiv.style.gap = '4px';
                    priestDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg><span>${escapeHtml(presiderName)}${isExternal ? ' <span style="margin-left:4px;" class="inline-block px-1.5 py-0.5 text-[10px] rounded bg-white/20 border border-white/30 align-middle">External</span>' : ''}</span>`;
                    wrapper.appendChild(priestDiv);
                }
            }

            return { domNodes: [wrapper] };
        },
        eventMouseEnter: function(info) {
            info.el.style.opacity = '0.9';
            info.el.style.transform = 'translateY(-2px)';
            info.el.style.transition = 'all 0.2s ease';
            info.el.style.zIndex = '100';

            // Create and show tooltip with formatted Mass Type
            const event = info.event;
            const tooltip = document.createElement('div');
            tooltip.className = 'fc-tooltip-home';

            // Get event position for smart positioning
            const rect = info.el.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            // Determine if event is in bottom rows or right side
            const isBottomRow = rect.bottom > viewportHeight - 200;
            const isRightSide = rect.right > viewportWidth - 250;

            // Position tooltip using fixed positioning with smart placement
            let tooltipStyles = `
                position: fixed;
                z-index: 99999;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 12px 16px;
                border-radius: 12px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.25);
                min-width: 200px;
                font-size: 14px;
                pointer-events: none;
            `;

            if (isBottomRow) {
                // Show tooltip above the event
                tooltipStyles += `bottom: ${viewportHeight - rect.top + 5}px;`;
            } else {
                // Show tooltip below the event
                tooltipStyles += `top: ${rect.bottom + 5}px;`;
            }

            if (isRightSide) {
                // Align tooltip to the right edge of the event
                tooltipStyles += `right: ${viewportWidth - rect.right}px;`;
            } else {
                // Align tooltip to the left edge of the event
                tooltipStyles += `left: ${rect.left}px;`;
            }

            tooltip.style.cssText = tooltipStyles;

            // Show ONLY Mass Type (mass_subtype) if available, otherwise show title
            let displayText = (event.extendedProps && event.extendedProps.scheduleData ? event.extendedProps.scheduleData.mass_subtype : null) || event.title;

            // Format the text: remove underscores and capitalize each word
            displayText = displayText
                .replace(/_/g, ' ')  // Replace underscores with spaces
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())  // Capitalize each word
                .join(' ');

            tooltip.innerHTML = `<strong style="font-size: 15px;">${escapeHtml(displayText)}</strong>`;
            document.body.appendChild(tooltip);

            // Store tooltip reference for cleanup
            info.el._tooltip = tooltip;
        },
        eventMouseLeave: function(info) {
            info.el.style.opacity = '1';
            info.el.style.transform = 'translateY(0)';
            info.el.style.zIndex = '1';

            // Remove tooltip from body
            if (info.el._tooltip) {
                info.el._tooltip.remove();
                info.el._tooltip = null;
            }
        }
    });

    homepageCalendar.render();
    console.log('Homepage calendar rendered successfully with', events.length, 'events');

    // Update total count
    document.getElementById('totalEvents').textContent = events.length;
}

// Render the Upcoming Events list (next 10 from today)
function renderHomepageUpcomingList(schedules) {
    const listEl = document.getElementById('homepageUpcomingList');
    if (!listEl) return;

    const today = new Date();
    const todayCut = new Date(today.getFullYear(), today.getMonth(), today.getDate());

    var items = [];
    var i, s;
    var arr = schedules || [];
    for (i = 0; i < arr.length; i++) {
        s = arr[i];
        var dateStr = (s.schedule_date || '').toString().slice(0, 10);
        var d = new Date(dateStr);
        if (isNaN(d)) continue;
        if (d < todayCut) continue;
        var copy = {};
        for (var k in s) { if (Object.prototype.hasOwnProperty.call(s, k)) { copy[k] = s[k]; } }
        copy._dateObj = d;
        items.push(copy);
    }

    items.sort(function(a, b) {
        if (a._dateObj - b._dateObj !== 0) return a._dateObj - b._dateObj;
        var at = String(a.start_time || '');
        var bt = String(b.start_time || '');
        if (at < bt) return -1;
        if (at > bt) return 1;
        return 0;
    });

    items = items.slice(0, 10);

    if (items.length === 0) {
        listEl.innerHTML = '<div class="text-gray-600 dark:text-gray-400 text-sm">No upcoming events.</div>';
        return;
    }

    var htmlParts = [];
    for (i = 0; i < items.length; i++) {
        s = items[i];
        var displayDate = s._dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        var timeStart = String(s.start_time || '').split(' ').pop().split('.')[0];
        var venueName = (s.venue && s.venue.name) ? s.venue.name : '';
        var locationText = venueName || (s.location || '');
        var color = getEventColor(s.event_type);
        htmlParts.push(
            '<button type="button" data-schedule-id="' + s.schedule_id + '" class="w-full text-left p-3 rounded-xl bg-white dark:bg-gray-800 border-2 border-emerald-200 dark:border-emerald-700 hover:border-emerald-400 dark:hover:border-emerald-500 transition flex items-start gap-3">' +
                '<span class="flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg text-white font-bold" style="background:' + color + ';">' + displayDate.replace(/[^\d]/g,'').padStart(2,'0') + '</span>' +
                '<span class="min-w-0">' +
                    '<span class="block text-sm font-black text-emerald-900 dark:text-emerald-200">' + escapeHtml(s.title || '') + '</span>' +
                    '<span class="block text-xs text-gray-600 dark:text-gray-400">' + escapeHtml(timeStart + (locationText ? ' • ' + locationText : '')) + '</span>' +
                '</span>' +
            '</button>'
        );
    }
    listEl.innerHTML = htmlParts.join('');

    var buttons = listEl.querySelectorAll('[data-schedule-id]');
    for (i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function() {
            var id = this.getAttribute('data-schedule-id');
            var selected = (schedules || []).find(function(x){ return String(x.schedule_id) === String(id); });
            if (selected) { renderHomeEventDetails(selected); }
        });
    }

    // End of renderHomepageUpcomingList
}

// Render details into the right-side panel (no modal)
function renderHomeEventDetails(schedule) {
    const panel = document.getElementById('homeEventDetailsBox');
    if (!panel) return;
    const venueName = (schedule && schedule.venue) ? schedule.venue.name : undefined;
    const locationText = venueName || schedule.location || '';
    const internalPriestName = (schedule && schedule.priest) ? schedule.priest.name : undefined;
    const externalPriestName = schedule ? schedule.external_priest_name : undefined;
    const presiderName = internalPriestName || externalPriestName || '';
    const isExternal = !internalPriestName && !!externalPriestName;
    const color = getEventColor(schedule.event_type);

    const timeStart = (schedule.start_time || '').split(' ').pop().split('.')[0];
    const timeEnd = (schedule.end_time || '').split(' ').pop().split('.')[0];
    const dateObj = new Date((schedule.schedule_date || '').toString().slice(0,10));
    const dateLabel = isNaN(dateObj) ? schedule.schedule_date : dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    const safeTitle = escapeHtml(schedule.title || '');
    const safeEventType = escapeHtml(String(schedule.event_type || '').replace(/_/g,' ').toUpperCase());
    const safeScheduleDate = escapeHtml(schedule.schedule_date || '');
    const safeDateLabel = escapeHtml(dateLabel || '');
    const safeTimeRange = escapeHtml(timeStart + (timeEnd ? ' - ' + timeEnd : ''));
    const safeLocationText = escapeHtml(locationText || '');
    const safePresiderName = escapeHtml(presiderName || '');
    const safeDescription = escapeHtml(schedule.description || '');

    panel.innerHTML = `
        <div class="rounded-2xl border-2" style="border-color:${color};">
            <div class="p-4 bg-white/80 dark:bg-gray-800/70 rounded-t-2xl">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-xl font-extrabold text-emerald-800 dark:text-emerald-200">${safeTitle}</h4>
                        <div class="mt-1 inline-flex items-center px-2 py-1 text-xs rounded-full text-white" style="background:${color};">
                            ${safeEventType}
                        </div>
                    </div>
                    <button class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white" onclick="homepageCalendarSetDate('${safeScheduleDate}')" title="View in calendar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </div>
            <div class="p-4 space-y-3 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 rounded-b-2xl">
                <p class="flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span><strong>Date:</strong> ${safeDateLabel}</span></p>
                <p class="flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span><strong>Time:</strong> ${safeTimeRange}</span></p>
                ${locationText ? `<p class=\"flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200\"><svg class=\"w-4 h-4 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z\"></path><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 11a3 3 0 11-6 0 3 3 0 016 0z\"></path></svg><span><strong>Location:</strong> ${safeLocationText}</span></p>` : ''}
                ${presiderName ? `<p class=\"flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200\"><svg class=\"w-4 h-4 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\"></path></svg><span><strong>Presider:</strong> ${safePresiderName}${isExternal ? ' <span class=\"ml-1 inline-block px-1.5 py-0.5 text-[10px] rounded bg-emerald-200/60 text-emerald-900 align-middle\">External</span>' : ''}</span></p>` : ''}
                ${schedule.description ? `<div class=\"text-sm text-gray-800 dark:text-gray-200\"><strong>Description:</strong> ${safeDescription}</div>` : ''}
            </div>
        </div>
    `;
    panel.classList.remove('hidden');
    // On smaller screens, ensure the panel is visible
    var panelWrap = document.getElementById('homeEventPanel');
    if (panelWrap && panelWrap.scrollIntoView) {
        panelWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function applyFilters() {
    const massCategoryFilter = document.getElementById('massCategoryFilter').value;
    const massTypeFilter = document.getElementById('massTypeFilter').value;

    console.log('🔍 Applying filters:', { category: massCategoryFilter, massType: massTypeFilter });

    // Store filtered schedules globally for the public calendar
    let filteredSchedules = allSchedulesData;

    // Filter by mass category (institutional_mass or non_institutional_mass)
    if (massCategoryFilter) {
        filteredSchedules = filteredSchedules.filter(schedule => {
            const matched = schedule.event_type === massCategoryFilter;
            if (matched) {
                console.log('✅ Category match:', schedule.title, '→', schedule.event_type);
            }
            return matched;
        });
        console.log(`📊 After category filter: ${filteredSchedules.length} of ${allSchedulesData.length} events`);
    }

    // Filter by specific mass type (by mass_subtype field, not title)
    if (massTypeFilter) {
        console.log('🔍 Filtering by mass_subtype:', massTypeFilter);
        filteredSchedules = filteredSchedules.filter(schedule => {
            const scheduleSubtype = schedule.mass_subtype || '';
            const matched = scheduleSubtype === massTypeFilter;

            console.log(`Checking schedule "${schedule.title}": mass_subtype="${scheduleSubtype}" vs filter="${massTypeFilter}" => ${matched ? '✅' : '❌'}`);

            return matched;
        });
        console.log(`📊 After mass type filter: ${filteredSchedules.length} events`);
    }

    console.log(`🎯 Final result: ${filteredSchedules.length} events to display`);
    console.log('Event titles:', filteredSchedules.map(s => s.title));

    // Store filtered schedules for the calendar
    window.filteredSchedules = filteredSchedules;

    // Destroy and recreate calendar with filtered events
    if (homepageCalendar) {
        homepageCalendar.destroy();
    }
    initializeHomeCalendar(filteredSchedules);

    // Update total count
    document.getElementById('totalEvents').textContent = filteredSchedules.length;
    // Update upcoming list under current filters
    renderHomepageUpcomingList(filteredSchedules);
}

function getEventColor(eventType) {
    const colors = {
        'institutional_mass': '#8B5CF6',
        'non_institutional_mass': '#3B82F6',
        'mass': '#8B5CF6',
        'confession': '#3B82F6',
        'adoration': '#F59E0B',
        'retreat': '#10B981',
        'seminar': '#EF4444',
        'meeting': '#6366F1',
        'celebration': '#EC4899',
        'other': '#6B7280'
    };
    return colors[eventType] || colors['other'];
}

// Jump home calendar to a date (YYYY-MM-DD or Date)
function homepageCalendarSetDate(dateStrOrObj) {
    if (!window.homepageCalendar) return;
    try {
        window.homepageCalendar.gotoDate(dateStrOrObj);
    } catch (e) {
        const d = new Date(dateStrOrObj);
        if (!isNaN(d)) {
            window.homepageCalendar.gotoDate(d);
        }
    }
}

// Initial upcoming list render after modules init (executed once DOM + hidden JSON present)
document.addEventListener('DOMContentLoaded', function() {
    renderHomepageUpcomingList(allSchedulesData || []);
});

// ===========================
// Reservation Calendar
// ===========================
function getReservationStatusColor(status) {
    var colors = {
        'pending': '#F59E0B',
        'adviser_approved': '#3B82F6',
        'approved': '#10B981',
        'completed': '#6B7280',
        'cancelled': '#EF4444',
        'rejected': '#DC2626'
    };
    return colors[status] || '#6B7280';
}

function getReservationStatusLabel(status) {
    var labels = {
        'pending': 'Pending',
        'adviser_approved': 'Adviser Approved',
        'approved': 'Approved',
        'completed': 'Completed',
        'cancelled': 'Cancelled',
        'rejected': 'Rejected'
    };
    return labels[status] || status;
}

function isPrivateReservation(res) {
    if (!res) return false;
    if (res.is_private_reservation === true) return true;
    return ['admin_approved', 'approved', 'confirmed'].indexOf(String(res.status || '').toLowerCase()) !== -1;
}

function getReservationOrganizations(res) {
    if (!res) return [];

    if (Array.isArray(res.organizations) && res.organizations.length > 0) {
        return res.organizations
            .map(function(org) { return org && org.org_name ? org.org_name : ''; })
            .filter(function(name) { return !!name; });
    }

    if (res.organization && res.organization.org_name) {
        return [res.organization.org_name];
    }

    return [];
}

function getReservationPriests(res) {
    if (!res) return [];

    var priests = [];

    if (Array.isArray(res.priests) && res.priests.length > 0) {
        for (var i = 0; i < res.priests.length; i++) {
            var p = res.priests[i] || {};
            var name = p.full_name || [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
            if (!name) continue;

            var isMain = Boolean(p.pivot && p.pivot.is_main_celebrant) || (res.officiant_id && Number(res.officiant_id) === Number(p.id));
            priests.push(isMain ? (name + ' (Main Celebrant)') : name);
        }
        return priests;
    }

    if (res.officiant) {
        var officiantName = res.officiant.full_name || [res.officiant.first_name, res.officiant.middle_name, res.officiant.last_name].filter(Boolean).join(' ');
        if (officiantName) {
            priests.push(officiantName);
            return priests;
        }
    }

    if (res.external_priest_name) {
        return [res.external_priest_name + ' (External)'];
    }

    return [];
}

function initializeReservationCalendar(reservations, schedules) {
    var calendarEl = document.getElementById('homepagecalendar');
    if (!calendarEl) return;

    var events = buildHomepageCalendarEvents(reservations, schedules);

    // Detect mobile for responsive settings
    var isMobile = window.innerWidth <= 768;

    homepageCalendar = new window.Calendar(calendarEl, {
        plugins: [window.dayGridPlugin, window.timeGridPlugin, window.listPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: '',
            right: 'title'
        },
        buttonText: { today: 'Today' },
        events: events,
        editable: false,
        selectable: false,
        dayMaxEvents: isMobile ? 3 : false, // Limit to 3 events on mobile, show +more link
        eventOrder: 'start',
        height: 'auto',
        contentHeight: isMobile ? 400 : 650,
        fixedWeekCount: false,
        showNonCurrentDates: false,
        displayEventTime: true,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        eventClick: function(info) {
            if (info.event.extendedProps.type === 'schedule') {
                renderHomeEventDetails(info.event.extendedProps.scheduleData);
                return;
            }

            showReservationModal(info.event.extendedProps.reservationData);
        },
        eventContent: function(arg) {
            var isMobile = window.innerWidth <= 768;
            var wrapper = document.createElement('div');
            var isScheduleEvent = arg.event.extendedProps.type === 'schedule';

            if (isScheduleEvent) {
                wrapper.style.padding = isMobile ? '2px 3px' : '6px 8px';
                wrapper.style.fontSize = isMobile ? '0.45rem' : '0.7rem';
                wrapper.style.lineHeight = isMobile ? '1.1' : '1.4';
                wrapper.style.cursor = 'pointer';
                wrapper.style.backgroundColor = arg.event.backgroundColor || '#6B7280';
                wrapper.style.border = '1px solid ' + (arg.event.borderColor || arg.event.backgroundColor || '#6B7280');
                wrapper.style.borderRadius = isMobile ? '3px' : '8px';
                wrapper.style.color = '#ffffff';
                wrapper.style.display = 'flex';
                wrapper.style.flexDirection = 'column';
                wrapper.style.gap = isMobile ? '1px' : '3px';
                wrapper.style.minHeight = isMobile ? '20px' : '60px';
                wrapper.style.overflow = 'hidden';

                var timeDiv = document.createElement('div');
                timeDiv.style.fontSize = isMobile ? '0.42rem' : '0.62rem';
                timeDiv.style.fontWeight = '600';
                timeDiv.style.opacity = '0.95';
                timeDiv.textContent = arg.timeText || '';
                wrapper.appendChild(timeDiv);

                var titleDiv = document.createElement('div');
                titleDiv.style.fontSize = isMobile ? '0.45rem' : '0.75rem';
                titleDiv.style.fontWeight = '700';
                titleDiv.style.lineHeight = '1.2';
                titleDiv.style.wordBreak = 'break-word';
                titleDiv.textContent = arg.event.title || 'Schedule';
                wrapper.appendChild(titleDiv);

                var venueName = (arg.event.extendedProps.scheduleData && arg.event.extendedProps.scheduleData.venue && arg.event.extendedProps.scheduleData.venue.name)
                    || arg.event.extendedProps.location
                    || '';
                if (venueName) {
                    var locationDiv = document.createElement('div');
                    locationDiv.style.fontSize = isMobile ? '0.42rem' : '0.62rem';
                    locationDiv.style.opacity = '0.95';
                    locationDiv.style.whiteSpace = 'nowrap';
                    locationDiv.style.overflow = 'hidden';
                    locationDiv.style.textOverflow = 'ellipsis';
                    locationDiv.textContent = venueName;
                    wrapper.appendChild(locationDiv);
                }

                return { domNodes: [wrapper] };
            }

            // Responsive styling - much more compact on mobile
            wrapper.style.padding = isMobile ? '2px 3px' : '6px 8px';
            wrapper.style.fontSize = isMobile ? '0.45rem' : '0.7rem';
            wrapper.style.lineHeight = isMobile ? '1.1' : '1.4';
            wrapper.style.cursor = 'pointer';
            wrapper.style.backgroundColor = arg.event.backgroundColor || '#6B7280';
            wrapper.style.border = '1px solid ' + (arg.event.borderColor || arg.event.backgroundColor || '#6B7280');
            wrapper.style.borderRadius = isMobile ? '3px' : '8px';
            wrapper.style.color = '#ffffff';
            wrapper.style.display = 'flex';
            wrapper.style.flexDirection = isMobile ? 'row' : 'column';
            wrapper.style.alignItems = isMobile ? 'center' : 'flex-start';
            wrapper.style.gap = isMobile ? '1px' : '3px';
            wrapper.style.minHeight = isMobile ? '20px' : '60px';
            wrapper.style.maxHeight = isMobile ? '20px' : 'auto';
            wrapper.style.overflow = 'hidden';
            wrapper.style.textOverflow = 'ellipsis';
            wrapper.style.fontWeight = '600';

            var isPrivate = !!(arg.event.extendedProps && arg.event.extendedProps.isPrivateReservation);

            if (isMobile) {
                // Mobile: Show only status dot + title in one line
                var statusDot = document.createElement('div');
                statusDot.style.width = '6px';
                statusDot.style.height = '6px';
                statusDot.style.borderRadius = '50%';
                statusDot.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
                statusDot.style.flexShrink = '0';
                statusDot.style.marginRight = '3px';
                wrapper.appendChild(statusDot);

                // Title only on mobile
                var titleDiv = document.createElement('div');
                titleDiv.style.fontSize = '0.45rem';
                titleDiv.style.fontWeight = '700';
                titleDiv.style.lineHeight = '1.1';
                titleDiv.style.overflow = 'hidden';
                titleDiv.style.textOverflow = 'ellipsis';
                titleDiv.style.whiteSpace = 'nowrap';
                titleDiv.style.flex = '1';
                titleDiv.style.minWidth = '0';
                titleDiv.textContent = isPrivate ? 'Occupied' : arg.event.title;
                wrapper.appendChild(titleDiv);
            } else {
                // Desktop: Full content as before
                var statusDiv = document.createElement('div');
                statusDiv.style.fontSize = '0.6rem';
                statusDiv.style.fontWeight = '700';
                statusDiv.style.textTransform = 'uppercase';
                statusDiv.style.letterSpacing = '0.05em';
                statusDiv.style.opacity = '0.9';
                statusDiv.style.display = 'flex';
                statusDiv.style.alignItems = 'center';
                statusDiv.style.gap = '3px';
                statusDiv.style.whiteSpace = 'nowrap';
                statusDiv.style.overflow = 'hidden';
                statusDiv.style.textOverflow = 'ellipsis';
                statusDiv.innerHTML = isPrivate
                    ? '<svg style="width:10px;height:10px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg><span>Occupied</span>'
                    : '<svg style="width:10px;height:10px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg><span>' + escapeHtml(getReservationStatusLabel(arg.event.extendedProps.status)) + '</span>';
                wrapper.appendChild(statusDiv);

                // Title
                var titleDiv = document.createElement('div');
                titleDiv.style.fontSize = '0.75rem';
                titleDiv.style.fontWeight = '700';
                titleDiv.style.lineHeight = '1.2';
                titleDiv.style.wordBreak = 'break-word';
                titleDiv.style.overflow = 'hidden';
                titleDiv.style.textOverflow = 'ellipsis';
                titleDiv.style.display = '-webkit-box';
                titleDiv.style.webkitLineClamp = '2';
                titleDiv.style.webkitBoxOrient = 'vertical';
                titleDiv.textContent = isPrivate ? 'Occupied' : arg.event.title;
                wrapper.appendChild(titleDiv);

                // Service name - desktop only
                if (!isPrivate && arg.event.extendedProps.serviceName) {
                    var svcDiv = document.createElement('div');
                    svcDiv.style.fontSize = '0.65rem';
                    svcDiv.style.opacity = '0.9';
                    svcDiv.style.display = 'flex';
                    svcDiv.style.alignItems = 'center';
                    svcDiv.style.gap = '3px';
                    svcDiv.style.overflow = 'hidden';
                    svcDiv.style.textOverflow = 'ellipsis';
                    svcDiv.style.whiteSpace = 'nowrap';
                    svcDiv.innerHTML = '<svg style="width:10px;height:10px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"/></svg><span>' + escapeHtml(arg.event.extendedProps.serviceName) + '</span>';
                    wrapper.appendChild(svcDiv);
                }

                // Venue - desktop only
                if (!isPrivate && arg.event.extendedProps.venueName) {
                    var venDiv = document.createElement('div');
                    venDiv.style.fontSize = '0.65rem';
                    venDiv.style.opacity = '0.9';
                    venDiv.style.display = 'flex';
                    venDiv.style.alignItems = 'center';
                    venDiv.style.gap = '3px';
                    venDiv.style.overflow = 'hidden';
                    venDiv.style.textOverflow = 'ellipsis';
                    venDiv.style.whiteSpace = 'nowrap';
                    venDiv.innerHTML = '<svg style="width:10px;height:10px;flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>' + escapeHtml(arg.event.extendedProps.venueName) + '</span>';
                    wrapper.appendChild(venDiv);
                }
            }

            return { domNodes: [wrapper] };
        },
        eventMouseEnter: function(info) {
            info.el.style.opacity = '0.9';
            info.el.style.transform = 'translateY(-2px)';
            info.el.style.transition = 'all 0.2s ease';
            info.el.style.zIndex = '100';
        },
        eventMouseLeave: function(info) {
            info.el.style.opacity = '1';
            info.el.style.transform = 'translateY(0)';
            info.el.style.zIndex = '1';
        }
    });

    homepageCalendar.render();
    document.getElementById('totalEvents').textContent = events.length;
}

// Render reservation detail into the right-side panel
function renderReservationEventDetails(res) {
    var panel = document.getElementById('homeEventDetailsBox');
    if (!panel) return;
    var privateReservation = isPrivateReservation(res);
    var venueName = (res.venue && res.venue.name) ? res.venue.name : (res.custom_venue_name || '');
    var serviceName = (res.service && res.service.service_name) ? res.service.service_name : '';
    var organizations = getReservationOrganizations(res);
    var priests = getReservationPriests(res);
    var color = getReservationStatusColor(res.status);
    var dateObj = new Date((res.schedule_date || '').toString().slice(0,10));
    var dateLabel = isNaN(dateObj) ? res.schedule_date : dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

    if (privateReservation) {
        panel.innerHTML =
            '<div class="rounded-2xl border-2" style="border-color:' + color + ';">' +
                '<div class="p-4 bg-white/80 dark:bg-gray-800/70 rounded-t-2xl">' +
                    '<h4 class="text-lg font-extrabold text-emerald-800 dark:text-emerald-200">Occupied</h4>' +
                    '<div class="mt-1 inline-flex items-center px-2 py-1 text-xs rounded-full text-white" style="background:' + color + ';">Reserved Slot</div>' +
                '</div>' +
                '<div class="p-4 space-y-2 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 rounded-b-2xl text-sm text-gray-800 dark:text-gray-200">' +
                    '<p><strong>Status:</strong> Admin-approved reservation</p>' +
                    '<p class="text-xs text-gray-600 dark:text-gray-400">Reservation details are hidden from the public calendar.</p>' +
                '</div>' +
            '</div>';
        panel.classList.remove('hidden');
        var privatePanelWrap = document.getElementById('homeEventPanel');
        if (privatePanelWrap && privatePanelWrap.scrollIntoView) {
            privatePanelWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        return;
    }

    panel.innerHTML =
        '<div class="rounded-2xl border-2" style="border-color:' + color + ';">' +
            '<div class="p-4 bg-white/80 dark:bg-gray-800/70 rounded-t-2xl">' +
                '<h4 class="text-lg font-extrabold text-emerald-800 dark:text-emerald-200">' + escapeHtml(res.activity_name || serviceName) + '</h4>' +
                '<div class="mt-1 inline-flex items-center px-2 py-1 text-xs rounded-full text-white" style="background:' + color + ';">' + escapeHtml(getReservationStatusLabel(res.status)) + '</div>' +
            '</div>' +
            '<div class="p-4 space-y-2 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 rounded-b-2xl text-sm text-gray-800 dark:text-gray-200">' +
                '<p><strong>Date:</strong> ' + escapeHtml(dateLabel) + '</p>' +
                (serviceName ? '<p><strong>Service:</strong> ' + escapeHtml(serviceName) + '</p>' : '') +
                (venueName ? '<p><strong>Venue:</strong> ' + escapeHtml(venueName) + '</p>' : '') +
                (organizations.length ? '<p><strong>Organization' + (organizations.length > 1 ? 's' : '') + ':</strong> ' + escapeHtml(organizations.join(', ')) + '</p>' : '') +
                (priests.length ? '<p><strong>Officiant' + (priests.length > 1 ? 's' : '') + ':</strong> ' + escapeHtml(priests.join(', ')) + '</p>' : '') +
                (res.purpose ? '<p><strong>Purpose:</strong> ' + escapeHtml(res.purpose) + '</p>' : '') +
                (res.participants_count ? '<p><strong>Participants:</strong> ' + escapeHtml(res.participants_count) + '</p>' : '') +
            '</div>' +
        '</div>';
    panel.classList.remove('hidden');
    var panelWrap = document.getElementById('homeEventPanel');
    if (panelWrap && panelWrap.scrollIntoView) {
        panelWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Show reservation details modal
function showReservationModal(res) {
    var modal = document.getElementById('reservationModal');
    var modalBody = document.getElementById('reservationModalBody');
    if (!modal || !modalBody || !res) return;

    if (isPrivateReservation(res)) {
        var privateColor = getReservationStatusColor(res.status);

        modalBody.innerHTML =
            '<div class="p-4 sm:p-5" style="border-top: 4px solid ' + privateColor + '; max-height: calc(90vh - 80px); overflow-y: auto;">' +
                '<div class="mb-3">' +
                    '<h3 class="text-lg sm:text-xl font-black text-emerald-800 dark:text-emerald-200 mb-2 pr-8 leading-tight">Occupied</h3>' +
                    '<div class="inline-flex items-center gap-2 px-2 py-1 text-xs font-bold rounded-full text-white" style="background:' + privateColor + ';">' +
                        '<span>Reserved Slot</span>' +
                    '</div>' +
                '</div>' +
                '<div class="space-y-2 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 rounded-lg p-3 text-xs sm:text-sm">' +
                    '<p class="text-gray-600 dark:text-gray-400 text-xs">Reservation details are hidden from the public calendar.</p>' +
                '</div>' +
            '</div>';

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        return;
    }

    var venueName = (res.venue && res.venue.name) ? res.venue.name : (res.custom_venue_name || 'Not specified');
    var serviceName = (res.service && res.service.service_name) ? res.service.service_name : 'Not specified';
    var organizations = getReservationOrganizations(res);
    var priests = getReservationPriests(res);
    var organizationsHtml = organizations.length > 1
        ? '<ul class="mt-1 space-y-0.5">' + organizations.map(function(org) { return '<li class="text-gray-600 dark:text-gray-400 text-xs">' + escapeHtml(org) + '</li>'; }).join('') + '</ul>'
        : '<p class="text-gray-600 dark:text-gray-400 text-xs">' + escapeHtml(organizations.length ? organizations[0] : 'Not specified') + '</p>';
    var priestsHtml = priests.length > 1
        ? '<ul class="mt-1 space-y-0.5">' + priests.map(function(priest) { return '<li class="text-gray-600 dark:text-gray-400 text-xs">' + escapeHtml(priest) + '</li>'; }).join('') + '</ul>'
        : '<p class="text-gray-600 dark:text-gray-400 text-xs">' + escapeHtml(priests.length ? priests[0] : 'Not assigned') + '</p>';
    var color = getReservationStatusColor(res.status);
    var statusLabel = getReservationStatusLabel(res.status);
    var dateObj = new Date((res.schedule_date || '').toString().slice(0,10));
    var dateLabel = isNaN(dateObj) ? res.schedule_date : dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    var timeStr = res.schedule_time || 'Not specified';
    if (timeStr && timeStr !== 'Not specified') {
        var timeParts = timeStr.split(':');
        if (timeParts.length >= 2) {
            var hours = parseInt(timeParts[0]);
            var minutes = timeParts[1];
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            timeStr = hours + ':' + minutes + ' ' + ampm;
        }
    }

    modalBody.innerHTML =
        '<div class="p-4 sm:p-5" style="border-top: 4px solid ' + color + '; max-height: calc(90vh - 80px); overflow-y: auto;">' +
            '<div class="mb-3">' +
                '<h3 class="text-lg sm:text-xl font-black text-emerald-800 dark:text-emerald-200 mb-2 pr-8 leading-tight">' + escapeHtml(res.activity_name || serviceName) + '</h3>' +
                '<div class="inline-flex items-center gap-2 px-2 py-1 text-xs font-bold rounded-full text-white" style="background:' + color + ';">' +
                    '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' +
                    '<span>' + escapeHtml(statusLabel) + '</span>' +
                '</div>' +
            '</div>' +
            '<div class="space-y-2 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 rounded-lg p-3 text-xs sm:text-sm">' +
                '<div class="flex items-start gap-2">' +
                    '<svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' +
                    '<div class="flex-1 min-w-0"><p class="font-semibold text-gray-700 dark:text-gray-300 text-xs">Date & Time</p><p class="text-gray-600 dark:text-gray-400 text-xs leading-snug">' + escapeHtml(dateLabel) + '<br><span class="text-emerald-600 dark:text-emerald-400 font-bold">' + escapeHtml(timeStr) + '</span></p></div>' +
                '</div>' +
                '<div class="flex items-start gap-2">' +
                    '<svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' +
                    '<div class="flex-1 min-w-0"><p class="font-semibold text-gray-700 dark:text-gray-300 text-xs">Service</p><p class="text-gray-600 dark:text-gray-400 text-xs truncate">' + escapeHtml(serviceName) + '</p></div>' +
                '</div>' +
                '<div class="flex items-start gap-2">' +
                    '<svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' +
                    '<div class="flex-1 min-w-0"><p class="font-semibold text-gray-700 dark:text-gray-300 text-xs">Venue</p><p class="text-gray-600 dark:text-gray-400 text-xs truncate">' + escapeHtml(venueName) + '</p></div>' +
                '</div>' +
                '<div class="flex items-start gap-2">' +
                    '<svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>' +
                    '<div class="flex-1 min-w-0"><p class="font-semibold text-gray-700 dark:text-gray-300 text-xs">Organization' + (organizations.length > 1 ? 's' : '') + '</p>' + organizationsHtml + '</div>' +
                '</div>' +
                '<div class="flex items-start gap-2">' +
                    '<svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>' +
                    '<div class="flex-1 min-w-0"><p class="font-semibold text-gray-700 dark:text-gray-300 text-xs">Officiant' + (priests.length > 1 ? 's' : '') + '</p>' + priestsHtml + '</div>' +
                '</div>' +
                (res.participants_count ?
                    '<div class="flex items-start gap-2">' +
                        '<svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' +
                        '<div class="flex-1"><p class="font-semibold text-gray-700 dark:text-gray-300 text-xs">Participants</p><p class="text-gray-600 dark:text-gray-400 text-xs">' + escapeHtml(res.participants_count) + '</p></div>' +
                    '</div>'
                : '') +
                (res.purpose ?
                    '<div class="flex items-start gap-2">' +
                        '<svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' +
                        '<div class="flex-1 min-w-0"><p class="font-semibold text-gray-700 dark:text-gray-300 text-xs">Purpose</p><p class="text-gray-600 dark:text-gray-400 text-xs line-clamp-2">' + escapeHtml(res.purpose) + '</p></div>' +
                    '</div>'
                : '') +
            '</div>' +
        '</div>';;

    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Close reservation details modal
function closeReservationModal() {
    var modal = document.getElementById('reservationModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    var modal = document.getElementById('reservationModal');
    if (modal && e.target === modal) {
        closeReservationModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReservationModal();
    }
});

// ===========================
// Reservation Filters
// ===========================
function applyReservationFilters() {
    var serviceVal = document.getElementById('resServiceFilter').value;
    var venueVal = document.getElementById('resVenueFilter').value;
    var statusVal = document.getElementById('resStatusFilter').value;

    function normalizeServiceToken(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function scheduleMatchesServiceFilter(schedule, selectedServiceId) {
        if (!selectedServiceId) return true;

        var rawMassSubtype = String((schedule && schedule.mass_subtype) || '').trim();
        if (!rawMassSubtype) return false;

        // Direct ID match (current staff UI stores service_id in mass_subtype)
        if (rawMassSubtype === String(selectedServiceId)) return true;

        // Legacy text/slug match fallback
        var serviceSelect = document.getElementById('resServiceFilter');
        var selectedText = '';
        if (serviceSelect) {
            var selectedOption = serviceSelect.querySelector('option[value="' + String(selectedServiceId) + '"]');
            selectedText = selectedOption ? selectedOption.textContent : '';
        }

        var normalizedMassSubtype = normalizeServiceToken(rawMassSubtype);
        var normalizedSelectedText = normalizeServiceToken(selectedText);

        return normalizedSelectedText && normalizedMassSubtype === normalizedSelectedText;
    }

    var filtered = allReservationsData;
    var filteredSchedules = allSchedulesData;

    if (serviceVal) {
        filtered = filtered.filter(function(r) {
            return String(r.service_id) === String(serviceVal);
        });

        filteredSchedules = filteredSchedules.filter(function(s) {
            return scheduleMatchesServiceFilter(s, serviceVal);
        });
    }

    if (venueVal) {
        filtered = filtered.filter(function(r) {
            return String(r.venue_id) === String(venueVal);
        });

        filteredSchedules = filteredSchedules.filter(function(s) {
            var scheduleVenueId = (s && s.venue_id) || (s && s.venue && s.venue.venue_id);
            return String(scheduleVenueId || '') === String(venueVal);
        });
    }

    if (statusVal) {
        filtered = filtered.filter(function(r) {
            return r.status === statusVal;
        });
    }

    console.log('Reservation filter:', { service: serviceVal, venue: venueVal, status: statusVal, results: filtered.length });

    // Update event count (filtered reservations + filtered public schedules)
    document.getElementById('totalEvents').textContent = filtered.length + filteredSchedules.length;

    if (homepageCalendar) {
        homepageCalendar.destroy();
    }
    initializeReservationCalendar(filtered, filteredSchedules);

    // Update upcoming list with reservation + schedule data
    renderReservationUpcomingList(filtered, filteredSchedules);
}

// Render upcoming reservations in the side panel list
function renderReservationUpcomingList(reservations, schedules) {
    var listEl = document.getElementById('homepageUpcomingList');
    if (!listEl) return;

    var today = new Date();
    var todayCut = new Date(today.getFullYear(), today.getMonth(), today.getDate());

    var items = [];
    for (var i = 0; i < reservations.length; i++) {
        var r = reservations[i];
        var dateStr = (r.schedule_date || '').toString().slice(0, 10);
        var d = new Date(dateStr);
        if (isNaN(d) || d < todayCut) continue;
        var copy = Object.assign({}, r);
        copy._dateObj = d;
        copy._entryType = 'reservation';
        items.push(copy);
    }

    for (var s = 0; s < (schedules || []).length; s++) {
        var sched = schedules[s];
        var schedDateStr = (sched.schedule_date || '').toString().slice(0, 10);
        var schedDate = new Date(schedDateStr);
        if (isNaN(schedDate) || schedDate < todayCut) continue;
        var schedCopy = Object.assign({}, sched);
        schedCopy._dateObj = schedDate;
        schedCopy._entryType = 'schedule';
        items.push(schedCopy);
    }

    items.sort(function(a, b) { return a._dateObj - b._dateObj; });
    items = items.slice(0, 10);

    if (items.length === 0) {
        listEl.innerHTML = '<div class="text-gray-600 dark:text-gray-400 text-sm">No upcoming events.</div>';
        return;
    }

    var htmlParts = [];
    for (var j = 0; j < items.length; j++) {
        var item = items[j];
        var displayDate = item._dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        var isSchedule = item._entryType === 'schedule';
        var color = isSchedule ? getEventColor(item.event_type) : getReservationStatusColor(item.status);
        var safeItemId = escapeHtml(isSchedule ? ('sched-' + item.schedule_id) : ('res-' + item.reservation_id));
        var safeTitle;
        var safeStatusLine;

        if (isSchedule) {
            var scheduleVenue = (item.venue && item.venue.name) ? item.venue.name : (item.location || '');
            safeTitle = escapeHtml(item.title || 'Schedule');
            safeStatusLine = escapeHtml((item.start_time || '') + (scheduleVenue ? ' \u2022 ' + scheduleVenue : ''));
        } else {
            var svcName = (item.service && item.service.service_name) ? item.service.service_name : '';
            var venName = (item.venue && item.venue.name) ? item.venue.name : '';
            var privateReservation = isPrivateReservation(item);
            safeTitle = escapeHtml(privateReservation ? 'Occupied' : (item.activity_name || svcName));
            safeStatusLine = escapeHtml(privateReservation
                ? 'Admin-approved reservation'
                : (getReservationStatusLabel(item.status) + (venName ? ' \u2022 ' + venName : '')));
        }

        htmlParts.push(
            '<button type="button" data-item-id="' + safeItemId + '" class="w-full text-left p-3 rounded-xl bg-white dark:bg-gray-800 border-2 border-emerald-200 dark:border-emerald-700 hover:border-emerald-400 dark:hover:border-emerald-500 transition flex items-start gap-3">' +
                '<span class="flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg text-white text-xs font-bold" style="background:' + color + ';">' + displayDate.replace(/[^\d]/g,'').padStart(2,'0') + '</span>' +
                '<span class="min-w-0">' +
                    '<span class="block text-sm font-black text-emerald-900 dark:text-emerald-200">' + safeTitle + '</span>' +
                    '<span class="block text-xs text-gray-600 dark:text-gray-400">' + safeStatusLine + '</span>' +
                '</span>' +
            '</button>'
        );
    }
    listEl.innerHTML = htmlParts.join('');

    var buttons = listEl.querySelectorAll('[data-item-id]');
    for (var k = 0; k < buttons.length; k++) {
        buttons[k].addEventListener('click', function() {
            var id = this.getAttribute('data-item-id');
            if (!id) return;

            if (id.indexOf('sched-') === 0) {
                var scheduleId = id.replace('sched-', '');
                var selectedSchedule = (schedules || []).find(function(x) { return String(x.schedule_id) === String(scheduleId); });
                if (selectedSchedule) renderHomeEventDetails(selectedSchedule);
                return;
            }

            var reservationId = id.replace('res-', '');
            var selectedReservation = reservations.find(function(x) { return String(x.reservation_id) === String(reservationId); });
            if (selectedReservation) showReservationModal(selectedReservation);
        });
    }
}
</script>

<style>
#homepagecalendar {
    font-family: inherit;
}

/* Prevent events from spanning across days */
#homepagecalendar .fc-daygrid-event-harness {
    position: relative !important;
    right: auto !important;
}

#homepagecalendar .fc-daygrid-event-harness-abs {
    position: relative !important;
    right: auto !important;
}

#homepagecalendar .fc-event-main {
    overflow: hidden !important;
}

#homepagecalendar .fc-toolbar-title {
    font-size: 1.75rem !important;
    font-weight: 900 !important;
    background: linear-gradient(90deg, #059669, #10B981, #34D399);
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
}

#homepagecalendar .fc-button {
    font-weight: 700 !important;
    border-radius: 0.75rem !important;
    padding: 0.5rem 1rem !important;
    text-transform: uppercase !important;
    font-size: 0.875rem !important;
    transition: all 0.2s ease !important;
}

#homepagecalendar .fc-button-primary {
    background: linear-gradient(135deg, #059669, #10B981) !important;
    border: none !important;
}

#homepagecalendar .fc-button-primary:hover {
    background: linear-gradient(135deg, #047857, #059669) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
}

#homepagecalendar .fc-button-primary:disabled {
    opacity: 0.5 !important;
}

#homepagecalendar .fc-daygrid-day-number {
    font-weight: 700 !important;
    font-size: 1rem !important;
    color: #374151;
}

#homepagecalendar .fc-col-header-cell {
    background: linear-gradient(135deg, #ECFDF5, #D1FAE5) !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    font-size: 0.875rem !important;
    padding: 1rem 0.5rem !important;
    color: #065F46 !important;
}

#homepagecalendar .fc-daygrid-day {
    min-height: 150px !important;
}

#homepagecalendar .fc-day-today {
    background: transparent !important;
}

#homepagecalendar .fc-event {
    border-radius: 0.5rem !important;
    border: none !important;
    margin: 2px 4px !important;
    padding: 4px !important;
    font-weight: 700 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
    white-space: normal !important;
    overflow: visible !important;
}

#homepagecalendar .fc-daygrid-event {
    white-space: normal !important;
    align-items: flex-start !important;
}

#homepagecalendar .fc-event-main {
    overflow: visible !important;
}

@media (max-width: 768px) {
    #homepagecalendar .fc-toolbar {
        flex-direction: column !important;
        gap: 0.5rem !important;
        padding: 0.5rem !important;
    }

    #homepagecalendar .fc-toolbar-title {
        font-size: 1rem !important;
        order: -1 !important;
    }

    #homepagecalendar .fc-toolbar-chunk {
        display: flex !important;
        justify-content: center !important;
    }

    #homepagecalendar .fc-button {
        font-size: 0.65rem !important;
        padding: 0.35rem 0.6rem !important;
        border-radius: 0.5rem !important;
    }

    #homepagecalendar .fc-col-header-cell {
        font-size: 0.6rem !important;
        padding: 0.35rem 0.1rem !important;
    }

    #homepagecalendar .fc-col-header-cell-cushion {
        padding: 0 !important;
    }

    #homepagecalendar .fc-daygrid-day {
        min-height: 100px !important;
    }

    #homepagecalendar .fc-daygrid-day-number {
        font-size: 0.6rem !important;
        padding: 2px 3px !important;
        position: sticky !important;
        top: 0 !important;
        background: white !important;
        z-index: 3 !important;
        border-radius: 0 0 4px 0 !important;
    }

    @media (prefers-color-scheme: dark) {
        #homepagecalendar .fc-daygrid-day-number {
            background: rgb(31, 41, 55) !important;
            color: rgb(209, 213, 219) !important;
        }
    }

    #homepagecalendar .fc-daygrid-day-frame {
        min-height: 100px !important;
    }

    #homepagecalendar .fc-event {
        margin: 3px 1px !important;
        padding: 0 !important;
        font-size: 0.45rem !important;
        min-height: 18px !important;
        max-height: 18px !important;
    }

    #homepagecalendar .fc-event-main {
        padding: 1px 2px !important;
        min-height: 18px !important;
        max-height: 18px !important;
        overflow: hidden !important;
    }

    #homepagecalendar .fc-daygrid-event-harness {
        margin: 2px 0 !important;
    }

    #homepagecalendar .fc-daygrid-day-events {
        margin-top: 16px !important;
        padding: 2px 1px !important;
    }

    #homepagecalendar .fc-daygrid-more-link {
        font-size: 0.55rem !important;
        font-weight: 700 !important;
        padding: 2px 4px !important;
        background: linear-gradient(135deg, #059669, #10B981) !important;
        color: white !important;
        border-radius: 4px !important;
        margin: 1px 2px !important;
        display: block !important;
        text-align: center !important;
    }

    /* Popover for "+more" link on mobile */
    #homepagecalendar .fc-popover {
        max-width: 90vw !important;
        font-size: 0.55rem !important;
    }

    #homepagecalendar .fc-popover-header {
        padding: 4px 6px !important;
        font-size: 0.6rem !important;
        font-weight: 700 !important;
    }

    #homepagecalendar .fc-popover-body {
        padding: 4px !important;
    }

    #homepagecalendar .fc-popover .fc-event {
        margin: 2px !important;
    }

    #homepagecalendar .fc-view-harness {
        min-height: 400px !important;
    }

    /* Compact header on mobile */
    #homepagecalendar .fc-header-toolbar {
        margin-bottom: 0.5rem !important;
    }

    /* Make scrollbar thin on mobile */
    #homepagecalendar .fc-scroller {
        scrollbar-width: thin;
    }
}

/* Extra small devices */
@media (max-width: 380px) {
    #homepagecalendar .fc-toolbar-title {
        font-size: 0.8rem !important;
    }

    #homepagecalendar .fc-button {
        font-size: 0.55rem !important;
        padding: 0.25rem 0.4rem !important;
    }

    #homepagecalendar .fc-col-header-cell {
        font-size: 0.5rem !important;
    }

    #homepagecalendar .fc-daygrid-day {
        min-height: 90px !important;
    }

    #homepagecalendar .fc-event {
        margin: 2px 1px !important;
    }

    #homepagecalendar .fc-daygrid-event-harness {
        margin: 1.5px 0 !important;
    }
}

/* Reservation Details Modal */
#reservationModal {
    animation: fadeIn 0.2s ease-out;
}

#reservationModalContent {
    animation: slideUp 0.3s ease-out;
    max-height: 85vh;
    overflow: hidden !important;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#reservationModal.hidden {
    display: none !important;
}

/* Modal responsive adjustments */
@media (max-width: 640px) {
    #reservationModalContent {
        margin: 0.5rem;
        max-height: 90vh;
        overflow: hidden !important;
    }

    #reservationModalBody {
        max-height: calc(90vh - 60px);
        overflow-y: auto;
    }
}
</style>
