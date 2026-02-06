@php
    // Get ALL liturgical schedules (both public and non-public)
    $allSchedules = \App\Models\LiturgicalSchedule::with(['priest', 'venue'])
        ->public() // show only public schedules on homepage
        ->orderBy('schedule_date')
        ->orderBy('start_time')
        ->get();
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

        <!-- Filter Section - Compact on Mobile -->
        <div class="mb-4 sm:mb-6 grid grid-cols-2 gap-2 sm:gap-4 max-w-4xl mx-auto">
            <!-- Service Category Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg p-3 sm:p-6 border-2 border-emerald-200 dark:border-emerald-700">
                <label for="massCategoryFilter" class="block text-xs sm:text-sm font-black text-gray-700 dark:text-gray-300 mb-2 sm:mb-3 flex items-center gap-1 sm:gap-2">
                    <svg class="w-3 h-3 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span class="hidden sm:inline">Service Category</span>
                    <span class="sm:hidden">Category</span>
                </label>
                <select id="massCategoryFilter" class="w-full px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-base border-2 border-gray-300 dark:border-gray-600 rounded-lg sm:rounded-xl shadow-sm focus:ring-4 focus:ring-purple-300 focus:border-purple-500 dark:bg-gray-700 dark:text-white font-semibold transition-all">
                    <option value="">All</option>
                    <option value="institutional_mass">⛪ Institutional</option>
                    <option value="non_institutional_mass">✝️ Non-Institutional</option>
                </select>
            </div>

            <!-- Mass Type Filter (Specific) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-lg p-3 sm:p-6 border-2 border-green-200 dark:border-green-700">
                <label for="massTypeFilter" class="block text-xs sm:text-sm font-black text-gray-700 dark:text-gray-300 mb-2 sm:mb-3 flex items-center gap-1 sm:gap-2">
                    <svg class="w-3 h-3 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Select Service Type</span>
                    <span class="sm:hidden">Type</span>
                </label>
                <select id="massTypeFilter" class="w-full px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-base border-2 border-gray-300 dark:border-gray-600 rounded-lg sm:rounded-xl shadow-sm focus:ring-4 focus:ring-indigo-300 focus:border-indigo-500 dark:bg-gray-700 dark:text-white font-semibold transition-all">
                    <option value="">All Types</option>
                    <optgroup label="Institutional Mass" id="institutionalOptions" style="display:none;">
                        <option value="university_opening_mass">University Opening Mass</option>
                        <option value="thanksgiving_mass">Thanksgiving Mass</option>
                        <option value="convocation_mass">Convocation Mass</option>
                        <option value="graduation_mass">Graduation Mass</option>
                        <option value="feast_day_masses">Feast Day Masses</option>
                        <option value="daily_noon_mass">Daily Noon Mass</option>
                        <option value="special_celebration_masses">Special Celebration Masses</option>
                    </optgroup>
                    <optgroup label="Non-Institutional Mass" id="nonInstitutionalOptions" style="display:none;">
                        <option value="memorial_requiem_masses">Memorial or Requiem Masses</option>
                        <option value="departmental_group_masses">Departmental or group-requested Masses</option>
                        <option value="recollection_masses">Recollection Masses</option>
                        <option value="novenas_devotions">Novenas and Devotions</option>
                        <option value="prayer_services_blessings">Prayer Services and Blessings</option>
                        <option value="special_devotional_masses">Special devotional Masses</option>
                        <option value="taize_prayer_services">Taize Prayer services</option>
                        <option value="sacraments">Sacraments</option>
                        <option value="community_outreach">Community outreach</option>
                    </optgroup>
                </select>
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
            <div class="bg-gradient-to-br from-teal-50 to-emerald-50 dark:from-teal-900/40 dark:to-emerald-900/40 rounded-xl sm:rounded-2xl p-3 sm:p-6 shadow-lg border-2 border-teal-200 dark:border-teal-700">
                <h3 class="text-sm sm:text-2xl font-black text-teal-800 dark:text-teal-300 mb-2 sm:mb-4 flex items-center gap-1 sm:gap-3">
                    <svg class="w-4 h-4 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Event Types</span>
                    <span class="sm:hidden">Types</span>
                </h3>
                <div class="grid grid-cols-1 gap-2 sm:gap-3">
                    <div class="flex items-center gap-2 text-xs sm:text-sm font-bold">
                        <span class="w-3 h-3 sm:w-4 sm:h-4 rounded-full" style="background-color: #8B5CF6;"></span>
                        <span class="text-gray-800 dark:text-gray-200"><span class="hidden sm:inline">⛪</span> Institutional</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs sm:text-sm font-bold">
                        <span class="w-3 h-3 sm:w-4 sm:h-4 rounded-full" style="background-color: #3B82F6;"></span>
                        <span class="text-gray-800 dark:text-gray-200"><span class="hidden sm:inline">✝️</span> Non-Institutional</span>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/40 dark:to-green-900/40 rounded-xl sm:rounded-2xl p-3 sm:p-6 shadow-lg border-2 border-emerald-200 dark:border-emerald-700">
                <h3 class="text-sm sm:text-2xl font-black text-emerald-800 dark:text-emerald-300 mb-2 sm:mb-4 flex items-center gap-1 sm:gap-3">
                    <svg class="w-4 h-4 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="hidden sm:inline">Quick Info</span>
                    <span class="sm:hidden">Info</span>
                </h3>
                <div class="space-y-1 sm:space-y-2 text-gray-700 dark:text-gray-300">
                    <p class="flex items-start gap-1 sm:gap-2 text-xs sm:text-sm font-semibold">
                        <span class="text-emerald-600 dark:text-emerald-400">•</span>
                        <span>Click events for details</span>
                    </p>
                    <p class="flex items-start gap-1 sm:gap-2 text-xs sm:text-sm font-semibold">
                        <span class="text-emerald-600 dark:text-emerald-400">•</span>
                        <span>Total: <span id="totalEvents" class="text-emerald-700 dark:text-emerald-300 font-black">{{ $allSchedules->count() }}</span></span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- JSON schedule data for homepage calendar -->
<script id="homeCalendarDataJson" type="application/json">{!! $allSchedules->toJson() !!}</script>

<script>
let homepageCalendar;
// Blade -> JS data bridge (avoid mentioning the Blade json directive here to prevent parsing)
var __homeJsonEl = document.getElementById('homeCalendarDataJson');
var allSchedulesData = [];
try {
    allSchedulesData = JSON.parse((__homeJsonEl && __homeJsonEl.textContent) ? __homeJsonEl.textContent : '[]');
} catch (e) {
    console.error('Failed to parse home calendar JSON:', e);
    allSchedulesData = [];
}

// Wait for both DOM and modules to be ready
function initWhenReady() {
    const calendarEl = document.getElementById('homepagecalendar');
    if (!calendarEl) {
        console.warn('Homepage calendar element not found');
        return;
    }

    console.log('🏠 Homepage calendar: Loading', allSchedulesData.length, 'total events');
    
    if (typeof window.Calendar === 'undefined' || 
        typeof window.dayGridPlugin === 'undefined' ||
        typeof window.timeGridPlugin === 'undefined' ||
        typeof window.listPlugin === 'undefined') {
        console.log('⏳ Waiting for FullCalendar modules to load...');
        setTimeout(initWhenReady, 100);
        return;
    }

    console.log('✅ FullCalendar modules loaded, initializing calendar...');

    // Initialize calendar with all events
    window.filteredSchedules = allSchedulesData;
    initializeHomeCalendar(allSchedulesData);
    
    // Setup filter listeners
    document.getElementById('massCategoryFilter').addEventListener('change', function() {
        handleCategoryChange();
        applyFilters();
    });
    document.getElementById('massTypeFilter').addEventListener('change', applyFilters);
    
    console.log('✅ Calendar initialized successfully');
}

// Start initialization when DOM is ready
document.addEventListener('DOMContentLoaded', initWhenReady);

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
                timeDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg><span>${timeStr}</span>`;
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
            titleDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0; margin-top: 2px;" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg><span>${arg.event.title}</span>`;
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
                locationDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0; margin-top: 1px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>${locationText}</span>`;
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
                    priestDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg><span>${presiderName}${isExternal ? ' <span style="margin-left:4px;" class="inline-block px-1.5 py-0.5 text-[10px] rounded bg-white/20 border border-white/30 align-middle">External</span>' : ''}</span>`;
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
            
            tooltip.innerHTML = `<strong style="font-size: 15px;">${displayText}</strong>`;
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
                    '<span class="block text-sm font-black text-emerald-900 dark:text-emerald-200">' + (s.title || '') + '</span>' +
                    '<span class="block text-xs text-gray-600 dark:text-gray-400">' + timeStart + (locationText ? ' • ' + locationText : '') + '</span>' +
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

    panel.innerHTML = `
        <div class="rounded-2xl border-2" style="border-color:${color};">
            <div class="p-4 bg-white/80 dark:bg-gray-800/70 rounded-t-2xl">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-xl font-extrabold text-emerald-800 dark:text-emerald-200">${schedule.title}</h4>
                        <div class="mt-1 inline-flex items-center px-2 py-1 text-xs rounded-full text-white" style="background:${color};">
                            ${String(schedule.event_type || '').replace(/_/g,' ').toUpperCase()}
                        </div>
                    </div>
                    <button class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white" onclick="homepageCalendarSetDate('${schedule.schedule_date}')" title="View in calendar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </div>
            <div class="p-4 space-y-3 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 rounded-b-2xl">
                <p class="flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span><strong>Date:</strong> ${dateLabel}</span></p>
                <p class="flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span><strong>Time:</strong> ${timeStart}${timeEnd ? ' - ' + timeEnd : ''}</span></p>
                ${locationText ? `<p class=\"flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200\"><svg class=\"w-4 h-4 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z\"></path><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15 11a3 3 0 11-6 0 3 3 0 016 0z\"></path></svg><span><strong>Location:</strong> ${locationText}</span></p>` : ''}
                ${presiderName ? `<p class=\"flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200\"><svg class=\"w-4 h-4 text-emerald-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\"></path></svg><span><strong>Presider:</strong> ${presiderName}${isExternal ? ' <span class=\"ml-1 inline-block px-1.5 py-0.5 text-[10px] rounded bg-emerald-200/60 text-emerald-900 align-middle\">External</span>' : ''}</span></p>` : ''}
                ${schedule.description ? `<div class=\"text-sm text-gray-800 dark:text-gray-200\"><strong>Description:</strong> ${schedule.description}</div>` : ''}
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
        min-height: 55px !important;
    }
    
    #homepagecalendar .fc-daygrid-day-number {
        font-size: 0.7rem !important;
        padding: 2px 4px !important;
    }
    
    #homepagecalendar .fc-daygrid-day-frame {
        min-height: 55px !important;
    }
    
    #homepagecalendar .fc-event {
        margin: 1px 2px !important;
        padding: 2px !important;
    }
    
    #homepagecalendar .fc-event-main-custom {
        padding: 3px 4px !important;
        font-size: 0.5rem !important;
        min-height: 40px !important;
        gap: 1px !important;
    }
    
    #homepagecalendar .fc-event-main-custom div {
        font-size: 0.5rem !important;
    }
    
    #homepagecalendar .fc-event-main-custom svg {
        width: 8px !important;
        height: 8px !important;
    }
    
    #homepagecalendar .fc-daygrid-event-harness {
        margin-top: 1px !important;
    }
    
    #homepagecalendar .fc-view-harness {
        min-height: 280px !important;
    }
    
    /* Hide location and priest on mobile to save space */
    #homepagecalendar .fc-event-main-custom > div:nth-child(n+3) {
        display: none !important;
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
        font-size: 0.85rem !important;
    }
    
    #homepagecalendar .fc-button {
        font-size: 0.6rem !important;
        padding: 0.25rem 0.4rem !important;
    }
    
    #homepagecalendar .fc-col-header-cell {
        font-size: 0.55rem !important;
    }
    
    #homepagecalendar .fc-daygrid-day {
        min-height: 48px !important;
    }
    
    #homepagecalendar .fc-daygrid-day-number {
        font-size: 0.6rem !important;
    }
}
</style>
