<x-app-layout>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    
    <style>
        /* Critical FullCalendar styles to ensure events display */
        .fc-event, .fc-event-dot {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .fc-daygrid-event {
            display: block !important;
            margin: 1px 0 !important;
            padding: 2px 4px !important;
            border-radius: 3px !important;
            font-size: 0.75rem !important;
            white-space: normal !important;
            position: relative !important;
        }
        
        .fc-daygrid-event-harness {
            display: block !important;
            margin-bottom: 2px !important;
            position: relative !important;
        }
        
        /* CRITICAL: Prevent events from spanning across multiple days */
        .fc-daygrid-event-harness-abs {
            position: relative !important;
            right: auto !important;
        }
        
        .fc-event-main {
            overflow: hidden !important;
        }
        
        .fc-daygrid-day-events {
            display: block !important;
        }
        
        .fc-event-title, .fc-event-time {
            display: inline !important;
            color: white !important;
        }
    </style>

    <x-slot name="header">
        <div>
            <h2 class="text-heading text-xl text-gray-800 dark:text-gray-200">
                Manage Liturgical Calendar
            </h2>
            <p class="text-muted text-sm mt-1">Add and manage public liturgical schedules and activities</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Validation Errors -->
            @if($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-400 text-red-800 dark:text-red-200 px-6 py-4 rounded-lg shadow-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">Validation Errors:</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Enhanced Success Message with Auto-Dismiss -->
            @if(session('success'))
                <div id="successAlert" 
                     class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/30 border-l-4 border-green-500 dark:border-green-400 text-green-800 dark:text-green-200 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 animate-slide-in-down"
                     x-data="{ show: true }"
                     x-show="show"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     x-init="setTimeout(() => show = false, 5000)">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-green-900 dark:text-green-100">Success!</p>
                                <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
                            </div>
                        </div>
                        <button @click="show = false" 
                                class="ml-4 flex-shrink-0 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Enhanced Add Schedule Button -->
            <div class="mb-6 flex justify-between items-center">
                <button onclick="openAddModal()" 
                        class="btn-primary shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="font-semibold">Add New Schedule</span>
                </button>
                <div class="text-sm text-muted">
                    Click on any date in the calendar to quickly add a schedule
                </div>
            </div>

            <!-- Calendar and Schedules -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                <!-- FullCalendar View -->
                <div class="xl:col-span-3">
                    <div class="card shadow-xl">
                        <div class="card-body p-6">
                            <div id="fullcalendar"></div>
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div class="card mt-6 shadow-lg">
                        <div class="card-header bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Event Types
                            </h3>
                        </div>
                        <div class="card-body p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded" style="background-color: #8B5CF6;"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Institutional Mass</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded" style="background-color: #3B82F6;"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Non-Institutional Mass</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded" style="background-color: #FCF3CF;"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Today's Date</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedules List Sidebar -->
                <div class="xl:col-span-1">
                    <div class="card sticky top-6 shadow-xl">
                        <div class="card-header bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                All Schedules
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="space-y-4 max-h-[800px] overflow-y-auto pr-2 custom-scrollbar">
                                @forelse($schedules as $schedule)
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-start justify-between mb-2">
                                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $schedule->title }}</h4>
                                            <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200">
                                                {{ ucfirst($schedule->event_type) }}
                                            </span>
                                        </div>
                                        
                                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mb-3">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $schedule->schedule_date->format('M d, Y') }}
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}
                                                @if($schedule->end_time)
                                                    - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                                @endif
                                            </div>
                                            @if($schedule->venue || $schedule->location)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    </svg>
                                                    {{ $schedule->venue ? $schedule->venue->name : $schedule->location }}
                                                </div>
                                            @endif
                                            @if($schedule->priest)
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    <span class="font-medium">{{ $schedule->priest->name }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        @if($schedule->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ Str::limit($schedule->description, 100) }}</p>
                                        @endif

                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="px-2 py-1 rounded bg-{{ $schedule->is_public ? 'green' : 'gray' }}-100 dark:bg-{{ $schedule->is_public ? 'green' : 'gray' }}-900/30 text-{{ $schedule->is_public ? 'green' : 'gray' }}-800 dark:text-{{ $schedule->is_public ? 'green' : 'gray' }}-200">
                                                {{ $schedule->is_public ? '👁️ Public' : '🔒 Private' }}
                                            </span>
                                        </div>

                                        <div class="flex gap-2 mt-3">
                                            <button onclick='openEditModal(@json($schedule))' class="flex-1 text-sm px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50">
                                                Edit
                                            </button>
                                            <form action="{{ route('staff.calendar.destroy', $schedule->schedule_id) }}" method="POST" class="flex-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Delete this schedule?')" class="w-full text-sm px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-900/50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p>No schedules yet</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Add Schedule Modal -->
    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all" onclick="event.stopPropagation()">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Add New Schedule</h3>
                            <p class="text-indigo-100 text-sm">Create a liturgical event or activity</p>
                        </div>
                    </div>
                    <button onclick="closeAddModal()" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('staff.calendar.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               required 
                               placeholder="e.g., Sunday Mass, Youth Retreat"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea name="description" 
                                  rows="3" 
                                  placeholder="Add details about this event..."
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="schedule_date" 
                                   required 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white dark:[color-scheme:dark] focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Event Type <span class="text-red-500">*</span>
                            </label>
                            <select name="event_type" 
                                    id="addEventType"
                                    required 
                                    x-data="{ massType: '' }"
                                    x-model="massType"
                                    @change="document.getElementById('addMassSubtype').value = ''; document.getElementById('addMassSubtypeContainer').style.display = (massType === 'institutional_mass' || massType === 'non_institutional_mass') ? 'block' : 'none';"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <option value="">Select Event Type</option>
                                <option value="institutional_mass">⛪ Institutional Mass</option>
                                <option value="non_institutional_mass">✝️ Non-Institutional Mass</option>
                            </select>
                        </div>
                        
                        <!-- Mass Subtype Dropdown (conditionally shown) -->
                        <div id="addMassSubtypeContainer" style="display: none;">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Mass Type <span class="text-red-500">*</span>
                            </label>
                            <select name="mass_subtype" 
                                    id="addMassSubtype"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <option value="">Select Mass Type</option>
                                <!-- Institutional Mass options -->
                                <optgroup label="Institutional Mass" id="addInstitutionalGroup" style="display: none;">
                                    @foreach($services->where('service_category', 'Institutional Mass') as $service)
                                        <option value="{{ $service->service_id }}">{{ $service->service_name }}</option>
                                    @endforeach
                                </optgroup>
                                <!-- Non-Institutional Mass options -->
                                <optgroup label="Non-Institutional Mass" id="addNonInstitutionalGroup" style="display: none;">
                                    @foreach($services->where('service_category', 'Non-Institutional Mass') as $service)
                                        <option value="{{ $service->service_id }}">{{ $service->service_name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <script>
                        // Show/hide mass subtype based on selection
                        document.getElementById('addEventType').addEventListener('change', function() {
                            const massSubtypeContainer = document.getElementById('addMassSubtypeContainer');
                            const massSubtype = document.getElementById('addMassSubtype');
                            const institutionalGroup = document.getElementById('addInstitutionalGroup');
                            const nonInstitutionalGroup = document.getElementById('addNonInstitutionalGroup');
                            
                            if (this.value === 'institutional_mass') {
                                massSubtypeContainer.style.display = 'block';
                                massSubtype.required = true;
                                institutionalGroup.style.display = 'block';
                                nonInstitutionalGroup.style.display = 'none';
                                massSubtype.value = '';
                            } else if (this.value === 'non_institutional_mass') {
                                massSubtypeContainer.style.display = 'block';
                                massSubtype.required = true;
                                institutionalGroup.style.display = 'none';
                                nonInstitutionalGroup.style.display = 'block';
                                massSubtype.value = '';
                            } else {
                                massSubtypeContainer.style.display = 'none';
                                massSubtype.required = false;
                                massSubtype.value = '';
                            }
                        });
                    </script>
                    

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Start Time <span class="text-red-500">*</span>
                            </label>
                            <input type="time" 
                                   name="start_time" 
                                   required 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white dark:[color-scheme:dark] focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">End Time</label>
                            <input type="time" 
                                   name="end_time" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white dark:[color-scheme:dark] focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Location</label>
                        <select id="venue_select" 
                                name="venue_select"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                onchange="handleVenueChange()">
                            <option value="">-- Select Location --</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->venue_id }}">{{ $venue->name }}</option>
                            @endforeach
                            <option value="custom">Custom Location (Outside)</option>
                        </select>
                    </div>

                    <!-- Custom Location Input (hidden by default) -->
                    <div id="custom_location_container" style="display: none;">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Custom Location</label>
                        <input type="text" 
                               id="custom_location_input"
                               name="location" 
                               placeholder="Enter custom location (e.g., Off-campus venue)"
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>

                    <!-- Hidden input to store venue_id -->
                    <input type="hidden" id="venue_id_input" name="venue_id" value="">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Assign Priest
                        </label>
                        <select name="priest_id" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="">-- Select Priest (Optional) --</option>
                            @foreach($priests as $priest)
                                <option value="{{ $priest->id }}">
                                    {{ $priest->name }} ({{ $priest->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg border border-indigo-200 dark:border-indigo-800">
                        <div class="flex items-start gap-3">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" 
                                   name="is_public" 
                                   id="is_public_add" 
                                   value="1"
                                   checked 
                                   class="mt-1 w-5 h-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500">
                            <label for="is_public_add" class="flex-1">
                                <span class="block font-semibold text-gray-900 dark:text-white">Make this schedule public</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Allow guests to view this event on the public calendar</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" 
                            onclick="closeAddModal()" 
                            class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-semibold transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Add Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="bg-blue-50 dark:bg-blue-900/20 px-6 py-4 border-b border-blue-100 dark:border-blue-800 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Edit Schedule</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <form id="editForm" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title *</label>
                        <input type="text" name="title" id="edit_title" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <textarea name="description" id="edit_description" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date *</label>
                            <input type="date" name="schedule_date" id="edit_date" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white dark:[color-scheme:dark]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Event Type *</label>
                            <select name="event_type" 
                                    id="edit_type" 
                                    required 
                                    onchange="handleEditEventTypeChange()"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                                <option value="">Select Event Type</option>
                                <option value="institutional_mass">⛪ Institutional Mass</option>
                                <option value="non_institutional_mass">✝️ Non-Institutional Mass</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Edit Mass Subtype Dropdown -->
                    <div id="editMassSubtypeContainer" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mass Type <span class="text-red-500">*</span>
                        </label>
                        <select name="mass_subtype" 
                                id="edit_mass_subtype"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">Select Mass Type</option>
                            <!-- Institutional Mass options -->
                            <optgroup label="Institutional Mass" id="editInstitutionalGroup">
                                @foreach($services->where('service_category', 'Institutional Mass') as $service)
                                    <option value="{{ $service->service_id }}">{{ $service->service_name }}</option>
                                @endforeach
                            </optgroup>
                            <!-- Non-Institutional Mass options -->
                            <optgroup label="Non-Institutional Mass" id="editNonInstitutionalGroup">
                                @foreach($services->where('service_category', 'Non-Institutional Mass') as $service)
                                    <option value="{{ $service->service_id }}">{{ $service->service_name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    
                    <script>
                        function handleEditEventTypeChange() {
                            const eventType = document.getElementById('edit_type').value;
                            const massSubtypeContainer = document.getElementById('editMassSubtypeContainer');
                            const massSubtype = document.getElementById('edit_mass_subtype');
                            const institutionalGroup = document.getElementById('editInstitutionalGroup');
                            const nonInstitutionalGroup = document.getElementById('editNonInstitutionalGroup');
                            
                            if (eventType === 'institutional_mass') {
                                massSubtypeContainer.style.display = 'block';
                                massSubtype.required = true;
                                institutionalGroup.style.display = 'block';
                                nonInstitutionalGroup.style.display = 'none';
                            } else if (eventType === 'non_institutional_mass') {
                                massSubtypeContainer.style.display = 'block';
                                massSubtype.required = true;
                                institutionalGroup.style.display = 'none';
                                nonInstitutionalGroup.style.display = 'block';
                            } else {
                                massSubtypeContainer.style.display = 'none';
                                massSubtype.required = false;
                                massSubtype.value = '';
                            }
                        }
                    </script>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Time *</label>
                            <input type="time" name="start_time" id="edit_start" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white dark:[color-scheme:dark]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Time</label>
                            <input type="time" name="end_time" id="edit_end" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white dark:[color-scheme:dark]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
                        <select id="edit_venue_select" 
                                name="edit_venue_select"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                onchange="handleEditVenueChange()">
                            <option value="">-- Select Location --</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->venue_id }}">{{ $venue->name }}</option>
                            @endforeach
                            <option value="custom">Custom Location (Outside)</option>
                        </select>
                    </div>

                    <!-- Custom Location Input for Edit (hidden by default) -->
                    <div id="edit_custom_location_container" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom Location</label>
                        <input type="text" 
                               id="edit_custom_location_input"
                               name="location" 
                               placeholder="Enter custom location"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Hidden input to store venue_id for edit -->
                    <input type="hidden" id="edit_venue_id_input" name="venue_id" value="">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Priest</label>
                        <select name="priest_id" id="edit_priest" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">-- Select Priest (Optional) --</option>
                            @foreach($priests as $priest)
                                <option value="{{ $priest->id }}">
                                    {{ $priest->name }} ({{ $priest->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_public" value="0">
                        <input type="checkbox" name="is_public" id="is_public_edit" value="1" class="w-4 h-4 text-indigo-600 rounded">
                        <label for="is_public_edit" class="text-sm text-gray-700 dark:text-gray-300">Make this schedule public (visible to guests)</label>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Update Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const schedules = @json($schedules);
        let selectedDate = null;

        // Initialize FullCalendar when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.initStaffCalendar === 'function') {
                window.initStaffCalendar(schedules);
            }
        });

        // Modal Functions
        function openAddModal(dateStr = null) {
            selectedDate = dateStr;
            const modal = document.getElementById('addModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Pre-fill date if clicked on calendar
            if (dateStr) {
                document.querySelector('input[name="schedule_date"]').value = dateStr;
            }
        }
        
        // Make openAddModal available globally for calendar.js
        window.openAddModal = openAddModal;

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            selectedDate = null;
        }

        // Handle venue selection in Add modal
        function handleVenueChange() {
            const venueSelect = document.getElementById('venue_select');
            const customContainer = document.getElementById('custom_location_container');
            const customInput = document.getElementById('custom_location_input');
            const venueIdInput = document.getElementById('venue_id_input');

            if (venueSelect.value === 'custom') {
                // Show custom location input
                customContainer.style.display = 'block';
                customInput.required = true;
                venueIdInput.value = '';
            } else if (venueSelect.value) {
                // Selected a venue from the list
                customContainer.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
                venueIdInput.value = venueSelect.value;
            } else {
                // No selection
                customContainer.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
                venueIdInput.value = '';
            }
        }

        // Handle venue selection in Edit modal
        function handleEditVenueChange() {
            const venueSelect = document.getElementById('edit_venue_select');
            const customContainer = document.getElementById('edit_custom_location_container');
            const customInput = document.getElementById('edit_custom_location_input');
            const venueIdInput = document.getElementById('edit_venue_id_input');

            if (venueSelect.value === 'custom') {
                // Show custom location input
                customContainer.style.display = 'block';
                customInput.required = true;
                venueIdInput.value = '';
            } else if (venueSelect.value) {
                // Selected a venue from the list
                customContainer.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
                venueIdInput.value = venueSelect.value;
            } else {
                // No selection
                customContainer.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
                venueIdInput.value = '';
            }
        }

        function openEditModal(schedule) {
            document.getElementById('edit_title').value = schedule.title;
            document.getElementById('edit_description').value = schedule.description || '';
            document.getElementById('edit_date').value = schedule.schedule_date;
            document.getElementById('edit_start').value = schedule.start_time.substring(0, 5);
            document.getElementById('edit_end').value = schedule.end_time ? schedule.end_time.substring(0, 5) : '';
            
            // Handle venue/location selection
            const editVenueSelect = document.getElementById('edit_venue_select');
            const editCustomContainer = document.getElementById('edit_custom_location_container');
            const editCustomInput = document.getElementById('edit_custom_location_input');
            const editVenueIdInput = document.getElementById('edit_venue_id_input');
            
            if (schedule.venue_id) {
                // Has a venue ID, select it from dropdown
                editVenueSelect.value = schedule.venue_id;
                editCustomContainer.style.display = 'none';
                editCustomInput.value = '';
                editVenueIdInput.value = schedule.venue_id;
            } else if (schedule.location) {
                // Has custom location
                editVenueSelect.value = 'custom';
                editCustomContainer.style.display = 'block';
                editCustomInput.value = schedule.location;
                editVenueIdInput.value = '';
            } else {
                // No location set
                editVenueSelect.value = '';
                editCustomContainer.style.display = 'none';
                editCustomInput.value = '';
                editVenueIdInput.value = '';
            }
            
            document.getElementById('edit_priest').value = schedule.priest_id || '';
            document.getElementById('edit_type').value = schedule.event_type;
            
            // Handle mass subtype for edit modal
            const editMassSubtype = document.getElementById('edit_mass_subtype');
            if (editMassSubtype && schedule.mass_subtype) {
                editMassSubtype.value = schedule.mass_subtype;
            }
            
            document.getElementById('is_public_edit').checked = schedule.is_public;
            document.getElementById('editForm').action = `/staff/calendar/${schedule.schedule_id}`;
            
            // Trigger event type change to show/hide mass subtype
            handleEditEventTypeChange();
            
            document.getElementById('editModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        // Make openEditModal available globally for calendar.js
        window.openEditModal = openEditModal;

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddModal();
                closeEditModal();
            }
        });

        // Close modals when clicking outside
        document.getElementById('addModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeAddModal();
        });

        document.getElementById('editModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        // Initialize calendar with schedules
        document.addEventListener('DOMContentLoaded', function() {
            const schedules = @json($schedules);
            console.log('=== CALENDAR DEBUG ===');
            console.log('Total schedules:', schedules.length);
            console.log('Schedules data:', schedules);
            console.log('FullCalendar element:', document.getElementById('fullcalendar'));
            console.log('initStaffCalendar function exists:', typeof window.initStaffCalendar);
            
            if (typeof window.initStaffCalendar === 'function') {
                console.log('Calling initStaffCalendar...');
                window.initStaffCalendar(schedules);
                console.log('Calendar initialized');
            } else {
                console.error('initStaffCalendar function not found');
            }
        });
    </script>
</x-app-layout>
