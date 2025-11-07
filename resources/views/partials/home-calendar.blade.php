@php
    // Get ALL liturgical schedules (both public and non-public)
    $allSchedules = \App\Models\LiturgicalSchedule::with(['priest', 'venue'])
        ->orderBy('schedule_date')
        ->orderBy('start_time')
        ->get();
@endphp

<section id="home-calendar" class="py-20 scroll-mt-28">
    <div class="container mx-auto px-4 max-w-7xl">
        
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900 dark:to-purple-900 backdrop-blur-sm rounded-full text-indigo-700 dark:text-indigo-300 mb-6 shadow-lg border-2 border-indigo-300 dark:border-indigo-600">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-black text-lg tracking-wide">Public Calendar</span>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
            <!-- Service Category Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border-2 border-purple-200 dark:border-purple-700">
                <label for="massCategoryFilter" class="block text-sm font-black text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Service Category
                </label>
                <select id="massCategoryFilter" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:ring-4 focus:ring-purple-300 focus:border-purple-500 dark:bg-gray-700 dark:text-white font-semibold transition-all">
                    <option value="">All Categories</option>
                    <option value="institutional_mass">⛪ Institutional Mass</option>
                    <option value="non_institutional_mass">✝️ Non-Institutional Mass</option>
                </select>
            </div>

            <!-- Mass Type Filter (Specific) -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border-2 border-indigo-200 dark:border-indigo-700">
                <label for="massTypeFilter" class="block text-sm font-black text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Select Service Type
                </label>
                <select id="massTypeFilter" class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:ring-4 focus:ring-indigo-300 focus:border-indigo-500 dark:bg-gray-700 dark:text-white font-semibold transition-all">
                    <option value="">Select Service Type</option>
                    <optgroup label="Institutional Mass" id="institutionalOptions" style="display:none;">
                        <option value="university_opening_mass">University Opening Mass</option>
                        <option value="thanksgiving_mass">Thanksgiving Mass</option>
                        <option value="convocation_mass">Convocation Mass</option>
                        <option value="graduation_mass">Graduation Mass</option>
                        <option value="feast_day_masses">Feast Day Masses</option>
                        <option value="memorial_requiem_masses">Memorial or Requiem Masses</option>
                        <option value="special_celebration_masses">Special Celebration Masses</option>
                    </optgroup>
                    <optgroup label="Non-Institutional Mass" id="nonInstitutionalOptions" style="display:none;">
                        <option value="daily_noon_mass">Daily Noon Mass</option>
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

        <div class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-indigo-900 dark:to-purple-900 rounded-3xl p-6 lg:p-10 shadow-2xl border-2 border-indigo-100 dark:border-indigo-800">
            <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
                <div id="homepagecalendar"></div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-teal-50 to-emerald-50 dark:from-teal-900/40 dark:to-emerald-900/40 rounded-2xl p-6 shadow-lg border-2 border-teal-200 dark:border-teal-700">
                <h3 class="text-2xl font-black text-teal-800 dark:text-teal-300 mb-4 flex items-center gap-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Event Types
                </h3>
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex items-center gap-2 text-sm font-bold">
                        <span class="w-4 h-4 rounded-full" style="background-color: #8B5CF6;"></span>
                        <span class="text-gray-800 dark:text-gray-200">⛪ Institutional Mass</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm font-bold">
                        <span class="w-4 h-4 rounded-full" style="background-color: #3B82F6;"></span>
                        <span class="text-gray-800 dark:text-gray-200">✝️ Non-Institutional Mass</span>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/40 dark:to-pink-900/40 rounded-2xl p-6 shadow-lg border-2 border-purple-200 dark:border-purple-700">
                <h3 class="text-2xl font-black text-purple-800 dark:text-purple-300 mb-4 flex items-center gap-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Quick Info
                </h3>
                <div class="space-y-2 text-gray-700 dark:text-gray-300">
                    <p class="flex items-start gap-2 text-sm font-semibold">
                        <span class="text-purple-600 dark:text-purple-400">•</span>
                        <span>Click on any event to view more details</span>
                    </p>
                    <p class="flex items-start gap-2 text-sm font-semibold">
                        <span class="text-purple-600 dark:text-purple-400">•</span>
                        <span>Use filters above to find specific events</span>
                    </p>
                    <p class="flex items-start gap-2 text-sm font-semibold">
                        <span class="text-purple-600 dark:text-purple-400">•</span>
                        <span>Total Events: <span id="totalEvents" class="text-purple-700 dark:text-purple-300 font-black">{{ $allSchedules->count() }}</span></span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
let homepageCalendar;
let allSchedulesData = @json($allSchedules);

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
            const event = info.event;
            const props = event.extendedProps;
            
            // Get venue name from relationship, fallback to location field
            const venueName = props.venue?.name;
            const locationText = venueName || props.location;
            
            const priestInfo = props.priest 
                ? `<p class="flex items-center gap-2 text-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg><span><strong>Priest:</strong> ${props.priest.name}</span></p>`
                : '';
            
            const locationInfo = locationText 
                ? `<p class="flex items-center gap-2 text-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg><span><strong>Location:</strong> ${locationText}</span></p>`
                : '';

            Swal.fire({
                title: event.title,
                html: `
                    <div class="text-left space-y-3 mt-4">
                        <p class="flex items-center gap-2 text-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span><strong>Time:</strong> ${event.start.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</span></p>
                        ${locationInfo}
                        ${priestInfo}
                        <p class="flex items-center gap-2 text-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg><span><strong>Type:</strong> ${props.eventType.replace('_', ' ').toUpperCase()}</span></p>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Close',
                confirmButtonColor: event.backgroundColor,
                customClass: {
                    popup: 'rounded-2xl',
                    title: 'text-2xl font-bold',
                    confirmButton: 'rounded-xl font-bold'
                }
            });
        },
        eventContent: function(arg) {
            let wrapper = document.createElement('div');
            wrapper.classList.add('fc-event-main-custom');
            
            // Apply event colors inline to ensure they display
            const evBg = arg.event.backgroundColor || getEventColor(arg.event.extendedProps?.eventType);
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
            const venueName = arg.event.extendedProps.scheduleData?.venue?.name || arg.event.extendedProps.venue?.name;
            const locationText = venueName || arg.event.extendedProps.scheduleData?.location || arg.event.extendedProps.location || '';
            
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
            
            // Add priest with icon if available
            if (arg.event.extendedProps.scheduleData?.priest || arg.event.extendedProps.priest) {
                const priestName = arg.event.extendedProps.scheduleData?.priest?.name || arg.event.extendedProps.priest?.name;
                if (priestName) {
                    let priestDiv = document.createElement('div');
                    priestDiv.style.fontSize = '0.73rem'; // Increased from 0.68rem
                    priestDiv.style.opacity = '0.95';
                    priestDiv.style.fontWeight = '600'; // Increased from 500
                    priestDiv.style.lineHeight = '1.3';
                    priestDiv.style.display = 'flex';
                    priestDiv.style.alignItems = 'center';
                    priestDiv.style.gap = '4px';
                    priestDiv.innerHTML = `<svg style="width: 12px; height: 12px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg><span>${priestName}</span>`;
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
            let displayText = event.extendedProps.scheduleData?.mass_subtype || event.title;
            
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
    color: #4F46E5;
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
    background: linear-gradient(135deg, #6366F1, #8B5CF6) !important;
    border: none !important;
}

#homepagecalendar .fc-button-primary:hover {
    background: linear-gradient(135deg, #4F46E5, #7C3AED) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4) !important;
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
    background: linear-gradient(135deg, #EEF2FF, #E0E7FF) !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    font-size: 0.875rem !important;
    padding: 1rem 0.5rem !important;
    color: #4F46E5 !important;
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
        gap: 1rem !important;
    }
    
    #homepagecalendar .fc-toolbar-title {
        font-size: 1.25rem !important;
    }
    
    #homepagecalendar .fc-daygrid-day {
        min-height: 80px !important;
    }
}
</style>
