<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-900 dark:to-gray-950 py-16">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-3 px-8 py-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-full text-emerald-700 dark:text-emerald-300 mb-6 shadow-lg border border-emerald-200 dark:border-emerald-700">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-bold text-lg">Public Calendar</span>
                </div>
                <h1 class="text-5xl sm:text-6xl font-extrabold text-emerald-700 dark:text-emerald-300 mb-4">📅 Parish Calendar</h1>
                <p class="text-xl text-gray-700 dark:text-gray-300 max-w-3xl mx-auto font-medium">
                    View our liturgical schedules and upcoming parish activities
                </p>
            </div>

            <!-- Full Width Calendar -->
            <div class="mb-10">
                <div class="flex items-center justify-end mb-3">
                    <button id="btnNextUpcoming" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-300 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        Next upcoming
                    </button>
                </div>
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden border-2 border-emerald-100 dark:border-emerald-800 hover:shadow-emerald-200 dark:hover:shadow-emerald-900 transition-all duration-300">
                    <div class="p-10">
                        <div id="publiccalendar"></div>
                    </div>
                </div>
            </div>

            <!-- Legend + Upcoming -->
            <div class="grid grid-cols-1 gap-10">
                <!-- Legend -->
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-3xl shadow-xl overflow-hidden border-2 border-emerald-100 dark:border-emerald-800 hover:shadow-emerald-200 dark:hover:shadow-emerald-900 transition-all duration-300">
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/30 dark:to-green-900/30 px-8 py-5 border-b-2 border-emerald-200 dark:border-emerald-700">
                        <h3 class="text-xl font-extrabold text-gray-900 dark:text-white flex items-center gap-3">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Event Types
                        </h3>
                    </div>
                    <div id="eventTypeLegend" class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="eventDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="closeEventModal()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full" onclick="event.stopPropagation()">
            <div id="modalContent"></div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    <script id="publicCalendarData" type="application/json">{!! json_encode($schedules) !!}</script>
    
    <script>
        // Shared event type maps for colors and labels
        const EVENT_TYPE_COLORS = {
            'mass': '#8B5CF6',
            'confession': '#3B82F6',
            'adoration': '#F59E0B',
            'retreat': '#10B981',
            'seminar': '#EF4444',
            'meeting': '#6366F1',
            'celebration': '#EC4899',
            'other': '#6B7280'
        };
        const EVENT_TYPE_LABELS = {
            'mass': '⛪ Mass',
            'confession': '✝️ Confession',
            'adoration': '🕯️ Adoration',
            'retreat': '🏔️ Retreat',
            'seminar': '📚 Seminar',
            'meeting': '👥 Meeting',
            'celebration': '🎉 Celebration',
            'other': '📌 Other'
        };

        // Initialize calendar when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const schedules = JSON.parse(document.getElementById('publicCalendarData').textContent || '[]');
            window.initPublicCalendar(schedules);

            // attach click handlers and styles for upcoming event rows
            document.querySelectorAll('.public-event-row').forEach(row => {
                // set dynamic border color from data attribute to avoid inline Blade CSS parsing issues
                const bc = row.getAttribute('data-border-color');
                if (bc) row.style.borderLeftColor = bc;
                row.addEventListener('click', () => {
                    const id = row.getAttribute('data-schedule-id');
                    if (!id) return;
                    window.showPublicEventDetails(parseInt(id, 10));
                });
            });
            // Next upcoming button: open modal for the earliest upcoming and navigate calendar if API exists
            const btnNext = document.getElementById('btnNextUpcoming');
            if (btnNext) {
                btnNext.addEventListener('click', () => {
                    const today = new Date();
                    const next = schedules
                        .map(s => ({...s, _d: new Date(s.schedule_date)}))
                        .filter(s => !isNaN(s._d))
                        .sort((a,b) => a._d - b._d)
                        .find(s => s._d >= new Date(today.getFullYear(), today.getMonth(), today.getDate()));
                    if (!next) return;
                    if (typeof window.publicCalendarSetDate === 'function') {
                        window.publicCalendarSetDate(next.schedule_date);
                    }
                    if (typeof window.showPublicEventModal === 'function') {
                        window.showPublicEventModal(next);
                    }
                    // Also sync the mini calendar selection if present
                    const btn = document.querySelector(`.pub-cal-day[data-date="${next.schedule_date}"]`);
                    if (btn) btn.click();
                });
            }

            // Build legend
            const legendEl = document.getElementById('eventTypeLegend');
            if (legendEl) {
                const types = Object.keys(EVENT_TYPE_COLORS);
                legendEl.innerHTML = types.map(t => `
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <span class="inline-block w-4 h-4 rounded" style="background-color: ${EVENT_TYPE_COLORS[t]}"></span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">${EVENT_TYPE_LABELS[t] || t}</span>
                    </div>
                `).join('');
            }

            // Note: Upcoming list replaced by mini calendar section below
        });

        // Function to show event details modal
        window.showPublicEventModal = function(schedule) {
            const modal = document.getElementById('eventDetailsModal');
            const content = document.getElementById('modalContent');
            
            const color = EVENT_TYPE_COLORS[schedule.event_type] || EVENT_TYPE_COLORS['other'];
            const typeLabel = EVENT_TYPE_LABELS[schedule.event_type] || '📌 Other';
            
            content.innerHTML = `
                <div class="p-6" style="border-top: 4px solid ${color}">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">${schedule.title}</h3>
                            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium text-white" style="background-color: ${color}">
                                ${typeLabel}
                            </span>
                        </div>
                        <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 ml-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>${new Date(schedule.schedule_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span>
                        </div>
                        
                        <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>${schedule.start_time}${schedule.end_time ? ' - ' + schedule.end_time : ''}</span>
                        </div>
                        
                        ${schedule.location ? `
                            <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                                <span>${schedule.location}</span>
                            </div>
                        ` : ''}
                    </div>
                    
                    ${schedule.description ? `
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Description</h4>
                            <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">${schedule.description}</p>
                        </div>
                    ` : ''}
                    
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button onclick="closeEventModal()" class="w-full px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.closeEventModal = function() {
            const modal = document.getElementById('eventDetailsModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        };

        window.showPublicEventDetails = function(scheduleId) {
            const schedules = JSON.parse(document.getElementById('publicCalendarData').textContent || '[]');
            const schedule = schedules.find(s => String(s.schedule_id) === String(scheduleId));
            if (schedule) {
                showPublicEventModal(schedule);
            }
        };
    </script>

    </x-guest-layout>
    