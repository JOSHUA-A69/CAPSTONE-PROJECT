<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-indigo-900 dark:to-purple-900 py-16">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-3 px-8 py-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-full text-indigo-700 dark:text-indigo-300 mb-6 shadow-lg border border-indigo-200 dark:border-indigo-700">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-bold text-lg">Public Calendar</span>
                </div>
                <h1 class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-400 dark:via-purple-400 dark:to-pink-400 mb-6">
                    📅 Parish Calendar
                </h1>
                <p class="text-2xl text-gray-700 dark:text-gray-300 max-w-3xl mx-auto font-medium">
                    View our liturgical schedules and upcoming parish activities
                </p>
            </div>

            <!-- Full Width Calendar -->
            <div class="mb-10">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden border-2 border-indigo-100 dark:border-indigo-800 hover:shadow-indigo-200 dark:hover:shadow-indigo-900 transition-all duration-300">
                    <div class="p-10">
                        <div id="publiccalendar"></div>
                    </div>
                </div>
            </div>

            <!-- Legend and Upcoming Events Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- Legend -->
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-3xl shadow-xl overflow-hidden border-2 border-purple-100 dark:border-purple-800 hover:shadow-purple-200 dark:hover:shadow-purple-900 transition-all duration-300">
                    <div class="bg-gradient-to-r from-indigo-100 via-purple-100 to-pink-100 dark:from-indigo-900/50 dark:via-purple-900/50 dark:to-pink-900/50 px-8 py-5 border-b-2 border-purple-200 dark:border-purple-700">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Event Types
                        </h3>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-2 gap-5">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                                <div class="w-6 h-6 rounded-lg shadow-md" style="background-color: #8B5CF6;"></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Mass</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                                <div class="w-6 h-6 rounded-lg shadow-md" style="background-color: #3B82F6;"></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Confession</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                                <div class="w-6 h-6 rounded-lg shadow-md" style="background-color: #F59E0B;"></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Adoration</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
                                <div class="w-6 h-6 rounded-lg shadow-md" style="background-color: #10B981;"></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Retreat</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                <div class="w-6 h-6 rounded-lg shadow-md" style="background-color: #EF4444;"></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Seminar</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">
                                <div class="w-6 h-6 rounded-lg shadow-md" style="background-color: #6366F1;"></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Meeting</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-pink-50 dark:bg-pink-900/20 hover:bg-pink-100 dark:hover:bg-pink-900/30 transition-colors">
                                <div class="w-6 h-6 rounded-lg shadow-md" style="background-color: #EC4899;"></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Celebration</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/20 hover:bg-gray-100 dark:hover:bg-gray-700/30 transition-colors">
                                <div class="w-6 h-6 rounded-lg shadow-md" style="background-color: #6B7280;"></div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Other</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-3xl shadow-xl border-2 border-pink-100 dark:border-pink-800 overflow-hidden hover:shadow-pink-200 dark:hover:shadow-pink-900 transition-all duration-300">
                    <div class="bg-gradient-to-r from-indigo-100 via-purple-100 to-pink-100 dark:from-indigo-900/50 dark:via-purple-900/50 dark:to-pink-900/50 px-8 py-5 border-b-2 border-pink-200 dark:border-pink-700">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Upcoming Events
                        </h3>
                    
                    <div class="p-8 max-h-[500px] overflow-y-auto custom-scrollbar">
                            @if($upcomingSchedules->isEmpty())
                                <div class="text-center py-16">
                                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 mb-4">
                                        <svg class="w-10 h-10 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-500 dark:text-gray-400">No upcoming events</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($upcomingSchedules as $schedule)
                                        <div class="border-l-4 pl-5 py-4 hover:bg-gradient-to-r hover:from-indigo-50/50 hover:to-purple-50/50 dark:hover:from-indigo-900/20 dark:hover:to-purple-900/20 rounded-r-2xl transition-all duration-300 cursor-pointer hover:shadow-lg hover:scale-[1.02] group"
                                             style="border-color: {{ $schedule->event_type ? match($schedule->event_type) {
                                                 'mass' => '#8B5CF6',
                                                 'confession' => '#3B82F6',
                                                 'adoration' => '#F59E0B',
                                                 'retreat' => '#10B981',
                                                 'seminar' => '#EF4444',
                                                 'meeting' => '#6366F1',
                                                 'celebration' => '#EC4899',
                                                 default => '#6B7280'
                                             } : '#6B7280' }}"
                                             onclick="showPublicEventDetails({{ $schedule->schedule_id }})">
                                            <h4 class="font-black text-gray-900 dark:text-white text-lg mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $schedule->title }}</h4>
                                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $schedule->schedule_date->format('M d, Y') }}
                                            </div>
                                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}
                                                @if($schedule->end_time)
                                                    - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                                @endif
                                            </div>
                                            @if($schedule->location)
                                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mt-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    </svg>
                                                    {{ $schedule->location }}
                                                </div>
                                            @endif
                                            @if($schedule->priest)
                                                <div class="flex items-center gap-2 text-sm text-indigo-600 dark:text-indigo-400 mt-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    <span class="font-semibold">{{ $schedule->priest->name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
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
    
    <script>
        // Initialize calendar when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const schedules = @json($schedules);
            window.initPublicCalendar(schedules);
        });

        // Function to show event details modal
        window.showPublicEventModal = function(schedule) {
            const modal = document.getElementById('eventDetailsModal');
            const content = document.getElementById('modalContent');
            
            const eventTypeColors = {
                'mass': '#8B5CF6',
                'confession': '#3B82F6',
                'adoration': '#F59E0B',
                'retreat': '#10B981',
                'seminar': '#EF4444',
                'meeting': '#6366F1',
                'celebration': '#EC4899',
                'other': '#6B7280'
            };
            
            const eventTypeLabels = {
                'mass': '⛪ Mass',
                'confession': '✝️ Confession',
                'adoration': '🕯️ Adoration',
                'retreat': '🏔️ Retreat',
                'seminar': '📚 Seminar',
                'meeting': '👥 Meeting',
                'celebration': '🎉 Celebration',
                'other': '📌 Other'
            };
            
            const color = eventTypeColors[schedule.event_type] || eventTypeColors['other'];
            const typeLabel = eventTypeLabels[schedule.event_type] || '📌 Other';
            
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
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>${new Date(schedule.schedule_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span>
                        </div>
                        
                        <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>${schedule.start_time}${schedule.end_time ? ' - ' + schedule.end_time : ''}</span>
                        </div>
                        
                        ${schedule.location ? `
                            <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <button onclick="closeEventModal()" class="w-full px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-colors">
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
            const schedules = @json($schedules);
            const schedule = schedules.find(s => s.schedule_id === scheduleId);
            if (schedule) {
                showPublicEventModal(schedule);
            }
        };
    </script>
</x-guest-layout>
