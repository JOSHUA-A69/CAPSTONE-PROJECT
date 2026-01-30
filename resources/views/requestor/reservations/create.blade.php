@extends('layouts.app')

@section('content')

<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Loading Overlay -->
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden" id="loadingOverlay">
            <div class="text-center text-white">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-white border-t-transparent"></div>
                <p class="mt-4 text-sm font-medium">Submitting your request...</p>
            </div>
        </div>

        <!-- Validation Errors Summary -->
        @if ($errors->any())
        <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Please correct the following errors:</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-2xl">
            
            <!-- Header Section -->
            <div class="p-8 text-center border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex flex-col items-center">
                    <div class="mb-4">
                        <div class="w-24 h-24 bg-indigo-600 rounded-full flex items-center justify-center shadow-lg p-1">
                            <div class="w-full h-full bg-white dark:bg-gray-800 rounded-full flex items-center justify-center p-2">
                                <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-full h-full object-contain" />
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
                        Spiritual Activity Request
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 text-sm max-w-xl mx-auto">
                        Please fill out the details below to schedule your spiritual activity. Requests must be submitted at least 7 days in advance.
                    </p>
                </div>
            </div>

            <!-- Help Section (Collapsible) -->
            <div class="border-b border-gray-200 dark:border-gray-700 bg-indigo-50 dark:bg-gray-900/50">
                <details class="group p-4" open>
                    <summary class="flex items-center justify-between cursor-pointer list-none text-sm font-medium text-indigo-700 dark:text-indigo-300">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Need help filling this form?
                        </span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="w-4 h-4" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-3 ml-7 space-y-1 transition-all duration-300">
                        <p><strong>Required fields are marked with <span class="text-red-500">*</span></strong></p>
                        <ul class="list-disc pl-4 space-y-1 opacity-80">
                            <li>Provide complete and accurate information</li>
                            <li>Requests must be submitted at least 7 days before the event</li>
                            <li>Write "N/A" in fields that don't apply to your request</li>
                        </ul>
                    </div>
                </details>
            </div>

            <form method="POST" action="{{ route('requestor.reservations.store') }}" id="reservationForm" novalidate class="p-6 md:p-8 space-y-8">
                @csrf

                <!-- Section 1: Basic Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-indigo-500 pl-3">Basic Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Activity Name -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="activity_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Name of Activity <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="activity_name" id="activity_name" value="{{ old('activity_name') }}" required maxlength="200"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                                placeholder="e.g., Send-Off Mass for BSET Board Takers">
                            <div class="flex justify-between mt-1">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Official name of your event</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400" id="activity_name_counter">0 / 200</span>
                            </div>
                            @error('activity_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Date -->
                        <div>
                            <label for="schedule_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date of Activity <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="schedule_date" id="schedule_date" value="{{ old('schedule_date') }}" required min="{{ date('Y-m-d', strtotime('+7 days')) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must be at least 7 days from today</p>
                            @error('schedule_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Time -->
                        <div>
                            <label for="schedule_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Time <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="schedule_time" id="schedule_time" value="{{ old('schedule_time', '08:00') }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                            @error('schedule_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Purpose/Theme -->
                        <div class="col-span-1 md:col-span-2">
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Theme</label>
                                    <textarea name="theme" id="theme" rows="3" maxlength="500" placeholder="e.g., Empowered by Faith, Guided to Serve"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">{{ old('theme') }}</textarea>
                                    <div class="text-right text-xs text-gray-500 dark:text-gray-400 mt-1" id="theme_counter">0 / 500</div>
                                </div>
                                <div>
                                    <label for="participants_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expected Participants</label>
                                    <input type="number" name="participants_count" id="participants_count" value="{{ old('participants_count') }}" min="1" max="10000" placeholder="e.g., 35"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Contact & Requesting Group -->
                <div class="space-y-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-indigo-500 pl-3">Contact Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <!-- Requesting Group -->
                         <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Requesting Office/Group <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                            </label>
                            <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700 max-h-48 overflow-y-auto checkbox-list">
                                @if($organizations->isEmpty())
                                    <p class="text-sm text-amber-600 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        No organizations available
                                    </p>
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @foreach($organizations as $o)
                                            <label class="flex items-center space-x-3 p-2 rounded hover:bg-white dark:hover:bg-gray-700 transition-colors cursor-pointer checkbox-item">
                                                <input type="checkbox" name="organization_ids[]" value="{{ $o->org_id }}"
                                                    @if(is_array(old('organization_ids')) && in_array($o->org_id, old('organization_ids'))) checked @endif
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <span class="text-sm text-gray-700 dark:text-gray-300 select-none">{{ $o->org_name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @error('organization_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                         </div>

                         <!-- Contact Person -->
                         <div>
                            <label for="contact_person" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Contact Person <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', auth()->user()->full_name) }}" required maxlength="100"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                            @error('contact_person') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                         </div>

                         <!-- Contact Number -->
                         <div>
                            <label for="contact_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Contact Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', auth()->user()->phone) }}" required maxlength="15" placeholder="09XX XXX XXXX"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                            @error('contact_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                         </div>
                    </div>
                </div>

                <!-- Section 3: Priest & Service -->
                <div class="space-y-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-indigo-500 pl-3">Service Details</h3>

                    <!-- Priest Selection -->
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-5 rounded-xl border border-indigo-100 dark:border-indigo-800/30">
                        <label for="priest_selection_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                             Officiant/Priest <span class="text-red-500">*</span>
                        </label>
                        <select name="priest_selection_type" id="priest_selection_type" required onchange="togglePriestOptions()"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors mb-4">
                            <option value="">-- Select Option --</option>
                            <option value="specific" @if(old('priest_selection_type')=='specific') selected @endif>Select from SVD Priests</option>
                            <option value="any_available" @if(old('priest_selection_type')=='any_available') selected @endif>Any Available Priest (Admin will assign)</option>
                            <option value="external" @if(old('priest_selection_type')=='external') selected @endif>Already Have a Priest (External)</option>
                        </select>

                        <!-- Specific Priest Selection -->
                        <div id="specific_priest_div" class="hidden mt-3 space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Choose Priest(s) <span class="text-red-500">*</span></label>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($priests as $priest)
                                    <label class="relative flex items-center p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all cursor-pointer group shadow-sm">
                                        <div class="flex items-center gap-3 w-full">
                                            <div class="flex-shrink-0">
                                                <img class="h-12 w-12 rounded-full object-cover border-2 border-gray-100 dark:border-gray-600 group-hover:border-indigo-200 dark:group-hover:border-indigo-500 transition-colors" 
                                                     src="{{ $priest->profile_picture_url }}" 
                                                     alt="{{ $priest->full_name }}">
                                            </div>
                                            <div class="flex-1 min-w-0 pr-6">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-indigo-700 dark:group-hover:text-indigo-300">
                                                    {{ $priest->full_name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">SVD Priest</p>
                                            </div>
                                            <div class="flex-shrink-0 ml-2">
                                                <input type="checkbox" name="priest_ids[]" value="{{ $priest->id }}"
                                                    @if(is_array(old('priest_ids')) && in_array($priest->id, old('priest_ids'))) checked @endif
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded transition-colors">
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Check multiple priests if co-celebration is needed
                            </p>
                        </div>

                        <!-- External Priest -->
                        <div id="external_priest_div" class="hidden mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priest Name <span class="text-red-500">*</span></label>
                                <input type="text" name="external_priest_name" id="external_priest_name" value="{{ old('external_priest_name') }}" placeholder="Enter priest's full name"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact (Optional)</label>
                                <input type="text" name="external_priest_contact" id="external_priest_contact" value="{{ old('external_priest_contact') }}" placeholder="Phone or email"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Info Messages -->
                        <div id="any_available_info" class="hidden mt-3 p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-lg text-sm border-l-4 border-blue-500">
                            <strong>ℹ️ Note:</strong> The admin will assign an available priest to your reservation and notify you once assigned.
                        </div>
                        <div id="external_priest_info" class="hidden mt-3 p-3 bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg text-sm border-l-4 border-green-500">
                            <strong>ℹ️ Note:</strong> Your reservation will be submitted for admin review. Please provide details of your external priest.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Service Category & Type -->
                         <div>
                            <div class="mb-4">
                                <label for="service_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Service Category <span class="text-red-500">*</span>
                                </label>
                                <select name="service_category" id="service_category" required onchange="toggleMassTypeField()"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                    <option value="">-- Select Service Category --</option>
                                    <option value="institutional_mass" data-requires-mass-type="true">⛪ Institutional Mass</option>
                                    <option value="non_institutional_mass" data-requires-mass-type="true">✝️ Non-Institutional Mass</option>
                                    <option value="other_services" data-requires-mass-type="true">📌 Other Services</option>
                                </select>
                            </div>

                            <div id="mass_type_container" class="hidden space-y-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Service Type <span class="text-red-500">*</span>
                                </label>
                                
                                <div id="service_dropdown_container" class="hidden">
                                    <select name="service_id" id="service_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                        <option value="">-- Select Service Type --</option>
                                        <optgroup label="Institutional Mass" id="institutional_mass_options" class="hidden">
                                            @foreach($services->where('service_category', 'Institutional Mass') as $service)
                                                <option value="{{ $service->service_id }}">{{ $service->service_name }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Non-Institutional Mass" id="non_institutional_mass_options" class="hidden">
                                            @foreach($services->where('service_category', 'Non-Institutional Mass') as $service)
                                                <option value="{{ $service->service_id }}">{{ $service->service_name }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                <div id="other_services_input_container" class="hidden">
                                    <input type="text" name="other_service_type" id="other_service_type" placeholder="Please enter the service type..." maxlength="50"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                </div>
                            </div>
                         </div>

                        <!-- Venue -->
                        <div>
                            <label for="venue_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Venue <span class="text-red-500">*</span>
                            </label>
                            <select name="venue_id" id="venue_select" required onchange="toggleCustomVenue()"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                                <option value="">-- Select Venue --</option>
                                @foreach($venues as $v)
                                    <option value="{{ $v->venue_id }}" @if(old('venue_id')==$v->venue_id) selected @endif>{{ $v->name }}</option>
                                @endforeach
                                <option value="custom" @if(old('venue_id')=='custom') selected @endif>Other/Custom</option>
                            </select>
                            
                            <div id="custom_venue_container" class="hidden mt-3">
                                <input type="text" name="custom_venue" id="custom_venue_input" placeholder="Specify exact location" maxlength="200"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                            </div>
                            @error('venue_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Celebration Reason -->
                        <div class="col-span-1 md:col-span-2">
                             <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason for the Celebration</label>
                             <textarea name="purpose" id="purpose" rows="3" maxlength="1000" placeholder="Brief purpose or reason for this celebration"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">{{ old('purpose') }}</textarea>
                             <div class="text-right text-xs text-gray-500 dark:text-gray-400 mt-1" id="purpose_counter">0 / 1000</div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Ministry Volunteers -->
                <div class="space-y-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-yellow-500 pl-3">
                        Ministry Volunteers
                        <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-2">(Write N/A if not applicable)</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Commentator</label>
                            <input type="text" name="commentator" value="{{ old('commentator') }}" placeholder="N/A" maxlength="100"
                                class="w-full rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Servers</label>
                            <input type="text" name="servers" value="{{ old('servers') }}" placeholder="N/A" maxlength="100"
                                class="w-full rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Choir</label>
                            <input type="text" name="choir" value="{{ old('choir') }}" placeholder="N/A" maxlength="100"
                                class="w-full rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Readers</label>
                            <input type="text" name="readers" value="{{ old('readers') }}" placeholder="N/A" maxlength="100"
                                class="w-full rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Psalmist</label>
                            <input type="text" name="psalmist" value="{{ old('psalmist') }}" placeholder="N/A" maxlength="100"
                                class="w-full rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Leader for Prayer</label>
                            <input type="text" name="prayer_leader" value="{{ old('prayer_leader') }}" placeholder="N/A" maxlength="100"
                                class="w-full rounded-md border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Section 5: Remarks -->
                <div class="space-y-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                     <label for="details" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Remarks/Other Requests
                    </label>
                    <textarea name="details" id="details" rows="3" maxlength="1000" placeholder="Include any additional information, special requests, or important notes..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">{{ old('details') }}</textarea>
                    <div class="text-right text-xs text-gray-500 dark:text-gray-400" id="details_counter">0 / 1000</div>
                </div>
                
                <!-- Footer Actions -->
                <div class="pt-8 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        Holy Name University - CREaM Office
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ route('requestor.reservations.index') }}" class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 text-center transition-all">
                            Cancel
                        </a>
                        <button type="submit" id="submitBtn" class="w-full sm:w-auto px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 dark:focus:ring-indigo-800 shadow-md transform transition-all hover:-translate-y-0.5 relative overflow-hidden">
                            <span id="submitText">Submit Request</span>
                            <span id="submitLoader" style="display: none;">
                                <svg class="inline w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Character counter function
    function updateCharCounter(textareaId, counterId, maxLength) {
        const textarea = document.getElementById(textareaId);
        const counter = document.getElementById(counterId);

        if (!textarea || !counter) return;

        const updateCount = () => {
            const length = textarea.value.length;
            counter.textContent = `${length} / ${maxLength} characters`;

            // Color coding
            counter.classList.remove('text-amber-500', 'text-red-500');
            if (length > maxLength * 0.9) {
                counter.classList.add('text-red-500');
            } else if (length > maxLength * 0.75) {
                counter.classList.add('text-amber-500');
            }
        };

        textarea.addEventListener('input', updateCount);
        updateCount(); // Initial count
    }

    // Custom venue toggle
    function toggleCustomVenue() {
        const venueSelect = document.getElementById('venue_select');
        const customContainer = document.getElementById('custom_venue_container');
        const customInput = document.getElementById('custom_venue_input');

        if (venueSelect.value === 'custom') {
            customContainer.classList.remove('hidden');
            customInput.required = true;
        } else {
            customContainer.classList.add('hidden');
            customInput.required = false;
            customInput.value = '';
        }
    }

    // Mass type field toggle
    function toggleMassTypeField() {
        const serviceCategorySelect = document.getElementById('service_category');
        const massTypeContainer = document.getElementById('mass_type_container');
        const serviceDropdownContainer = document.getElementById('service_dropdown_container');
        const otherServicesInputContainer = document.getElementById('other_services_input_container');
        const serviceIdSelect = document.getElementById('service_id');
        const otherServiceTypeInput = document.getElementById('other_service_type');
        const institutionalOptions = document.getElementById('institutional_mass_options');
        const nonInstitutionalOptions = document.getElementById('non_institutional_mass_options');
        
        const selectedOption = serviceCategorySelect.options[serviceCategorySelect.selectedIndex];
        const requiresMassType = selectedOption.getAttribute('data-requires-mass-type') === 'true';
        
        if (requiresMassType) {
            massTypeContainer.classList.remove('hidden');
            
            // Show appropriate mass type options
            if (serviceCategorySelect.value === 'institutional_mass') {
                serviceDropdownContainer.classList.remove('hidden');
                otherServicesInputContainer.classList.add('hidden');
                serviceIdSelect.required = true;
                otherServiceTypeInput.required = false;
                institutionalOptions.classList.remove('hidden');
                nonInstitutionalOptions.classList.add('hidden');
                serviceIdSelect.value = '';
                otherServiceTypeInput.value = '';
            } else if (serviceCategorySelect.value === 'non_institutional_mass') {
                serviceDropdownContainer.classList.remove('hidden');
                otherServicesInputContainer.classList.add('hidden');
                serviceIdSelect.required = true;
                otherServiceTypeInput.required = false;
                institutionalOptions.classList.add('hidden');
                nonInstitutionalOptions.classList.remove('hidden');
                serviceIdSelect.value = '';
                otherServiceTypeInput.value = '';
            } else if (serviceCategorySelect.value === 'other_services') {
                serviceDropdownContainer.classList.add('hidden');
                otherServicesInputContainer.classList.remove('hidden');
                serviceIdSelect.required = false;
                otherServiceTypeInput.required = true;
                institutionalOptions.classList.add('hidden');
                nonInstitutionalOptions.classList.add('hidden');
                serviceIdSelect.value = '';
                otherServiceTypeInput.value = '';
            }
        } else {
            massTypeContainer.classList.add('hidden');
            serviceDropdownContainer.classList.add('hidden');
            otherServicesInputContainer.classList.add('hidden');
            serviceIdSelect.required = false;
            otherServiceTypeInput.required = false;
            serviceIdSelect.value = '';
            otherServiceTypeInput.value = '';
            institutionalOptions.classList.add('hidden');
            nonInstitutionalOptions.classList.add('hidden');
        }
    }

    // Form validation
    function validateForm() {
        const form = document.getElementById('reservationForm');
        let isValid = true;
        let errorMessages = [];

        // Check required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            // Skip hidden fields
            if (field.offsetParent === null && field.type !== 'hidden') {
                return;
            }
            
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
                
                // Get field label - clean version
                let label = getFieldLabel(field);
                if (label && !errorMessages.includes(label)) {
                    errorMessages.push(label);
                }
            } else {
                field.classList.remove('border-red-500');
            }
        });

        // Validate date (must be at least 7 days from now)
        const dateInput = document.getElementById('schedule_date');
        if (dateInput && dateInput.value) {
            const selectedDate = new Date(dateInput.value);
            selectedDate.setHours(0, 0, 0, 0);

            const minDate = new Date();
            minDate.setDate(minDate.getDate() + 7);
            minDate.setHours(0, 0, 0, 0);

            if (selectedDate < minDate) {
                dateInput.classList.add('border-red-500');
                errorMessages.push('Event date must be at least 7 days from today');
                isValid = false;
            }
        }

        // Validate Organization Selection (Optional now)
        const orgCheckboxes = document.querySelectorAll('input[name="organization_ids[]"]');
        if (orgCheckboxes.length > 0) {
             const orgContainer = orgCheckboxes[0].closest('.checkbox-list');
             if (orgContainer) {
                orgContainer.classList.remove('border-red-500', 'bg-red-50');
             }
        }


        // Validate Priest Selection based on Type
        const priestTypeSelect = document.getElementById('priest_selection_type');
        if (priestTypeSelect) {
            const type = priestTypeSelect.value;
            
            if (type === 'specific') {
                const priestCheckboxes = document.querySelectorAll('input[name="priest_ids[]"]');
                let onePriestChecked = false;
                if(priestCheckboxes.length > 0) {
                    priestCheckboxes.forEach(cb => {
                        if (cb.checked) onePriestChecked = true;
                    });
                    
                    if (!onePriestChecked) {
                        const priestContainer = priestCheckboxes[0].closest('.checkbox-list');
                         if (priestContainer) {
                            priestContainer.classList.add('border-red-500', 'bg-red-50');
                         }
                        errorMessages.push('Please select at least one SVD Priest');
                        isValid = false; 
                    } else {
                         const priestContainer = priestCheckboxes[0].closest('.checkbox-list');
                         if (priestContainer) {
                            priestContainer.classList.remove('border-red-500', 'bg-red-50');
                         }
                    }
                }
            } else if (type === 'external') {
                 const extName = document.getElementById('external_priest_name');
                 if (extName && !extName.value.trim()) {
                      extName.classList.add('border-red-500');
                       if (!errorMessages.includes('External Priest Name')) {
                            errorMessages.push('External Priest Name is required');
                       }
                      isValid = false;
                 }
            }
        }

        // Validate phone number format
        const phoneInput = document.getElementById('contact_number');
        if (phoneInput && phoneInput.value) {
            const phonePattern = /^[0-9+\-\s()]+$/;
            if (!phonePattern.test(phoneInput.value)) {
                phoneInput.classList.add('border-red-500');
                errorMessages.push('Please enter a valid phone number');
                isValid = false;
            }
        }

        // Show error modal if validation fails
        if (!isValid) {
            showValidationErrorModal(errorMessages);
        }

        return isValid;
    }
    
    // Get clean field label
    function getFieldLabel(field) {
        const fieldLabels = {
            'activity_name': 'Activity Name',
            'schedule_date': 'Date of Activity',
            'schedule_time': 'Time',
            'contact_person': 'Contact Person',
            'contact_number': 'Contact Number',
            'officiant_id': 'Officiant/Priest',
            'service_category': 'Service Category',
            'service_id': 'Service Type',
            'other_service_type': 'Service Type',
            'venue_select': 'Venue',
            'venue_id': 'Venue',
            'custom_venue': 'Custom Venue',
            'purpose': 'Reason for Celebration',
            'theme': 'Theme',
            'details': 'Additional Details',
            'organization_id': 'Organization',
            'priest_selection_type': 'Priest Selection',
            'external_priest_name': 'External Priest Name'
        };
        
        if (fieldLabels[field.id]) {
            return fieldLabels[field.id];
        }
        if (fieldLabels[field.name]) {
            return fieldLabels[field.name];
        }
        
        let name = field.name || field.id || 'Field';
        return name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Show validation error modal
    function showValidationErrorModal(errors) {
        const existingModal = document.getElementById('validationErrorModal');
        if (existingModal) {
            existingModal.remove();
        }

        const uniqueErrors = [...new Set(errors)];
        const displayErrors = uniqueErrors.slice(0, 6);
        const errorListHtml = displayErrors.map(err => `
            <li class="flex items-center gap-2 py-2 border-b border-gray-100 last:border-0 text-sm text-gray-700">
                <span class="text-red-500 font-bold">•</span>
                <span>${err}</span>
            </li>
        `).join('');
        
        const remainingCount = uniqueErrors.length - displayErrors.length;
        
        const modalHtml = `
            <div id="validationErrorModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 animate-fade-in" onclick="if(event.target === this) closeValidationErrorModal()">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden animate-slide-up">
                    <div class="bg-red-50 dark:bg-red-900/30 p-6 text-center border-b border-red-100 dark:border-red-800/50">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50 mb-4">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-red-900 dark:text-red-100">Action Required</h3>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">Please complete the following fields</p>
                    </div>
                    
                    <div class="p-6">
                        <ul class="max-h-48 overflow-y-auto custom-scrollbar">
                            ${errorListHtml}
                        </ul>
                        ${remainingCount > 0 ? `
                            <p class="text-center text-xs text-gray-400 mt-3">+ ${remainingCount} more field${remainingCount > 1 ? 's' : ''}</p>
                        ` : ''}
                    </div>
                    
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50">
                        <button onclick="closeValidationErrorModal()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-3 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                            OK, I'll fix it
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        resetSubmitButton();
    }
    
    function resetSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        if (submitBtn) submitBtn.disabled = false;
        if (submitText) submitText.style.display = 'inline';
        if (submitLoader) submitLoader.style.display = 'none';
        if (loadingOverlay) loadingOverlay.classList.remove('hidden');
        if (loadingOverlay) loadingOverlay.classList.add('hidden');
    }

    function closeValidationErrorModal() {
        const modal = document.getElementById('validationErrorModal');
        if (modal) {
            modal.remove();
        }
        
        const form = document.getElementById('reservationForm');
        const firstError = form.querySelector('.border-red-500');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => firstError.focus(), 300);
        }
    }

    function addFieldValidation(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.addEventListener('blur', function() {
            if (field.hasAttribute('required')) {
                if (field.value.trim()) {
                    field.classList.remove('border-red-500');
                } else {
                    field.classList.add('border-red-500');
                }
            }
        });

        field.addEventListener('focus', function() {
            field.classList.remove('border-red-500');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomVenue();
        toggleMassTypeField();

        updateCharCounter('activity_name', 'activity_name_counter', 200);
        updateCharCounter('theme', 'theme_counter', 500);
        updateCharCounter('purpose', 'purpose_counter', 1000);
        updateCharCounter('details', 'details_counter', 1000);

        const validationFields = [
            'activity_name', 'schedule_date', 'schedule_time',
            'contact_person', 'contact_number', 'officiant_id',
            'service_id', 'venue_select'
        ];

        validationFields.forEach(fieldId => addFieldValidation(fieldId));

        const form = document.getElementById('reservationForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');
        const loadingOverlay = document.getElementById('loadingOverlay');

        submitBtn.disabled = false;
        submitText.style.display = 'inline';
        submitLoader.style.display = 'none';
        if (loadingOverlay) loadingOverlay.classList.add('hidden');

        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                resetSubmitButton();
                return false;
            }

            if (typeof currentAvailabilityStatus !== 'undefined' && !currentAvailabilityStatus.available) {
                e.preventDefault();
                let conflictMsg = 'Please resolve the scheduling conflicts before submitting:\n\n';
                if (currentAvailabilityStatus.messages && currentAvailabilityStatus.messages.length > 0) {
                    currentAvailabilityStatus.messages.forEach(msg => {
                        conflictMsg += '• ' + msg + '\n';
                    });
                } else {
                    conflictMsg += '• The selected time slot is not available';
                }
                alert(conflictMsg);
                resetSubmitButton();
                return false;
            }

            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoader.style.display = 'inline';
            if (loadingOverlay) loadingOverlay.classList.remove('hidden');
        });
    });

    function togglePriestOptions() {
        const selectionType = document.getElementById('priest_selection_type').value;
        const specificDiv = document.getElementById('specific_priest_div');
        const externalDiv = document.getElementById('external_priest_div');
        const anyAvailableInfo = document.getElementById('any_available_info');
        const externalInfo = document.getElementById('external_priest_info');
        const externalNameInput = document.getElementById('external_priest_name');

        if (specificDiv) specificDiv.classList.add('hidden');
        if (externalDiv) externalDiv.classList.add('hidden');
        if (anyAvailableInfo) anyAvailableInfo.classList.add('hidden');
        if (externalInfo) externalInfo.classList.add('hidden');

        if (externalNameInput) externalNameInput.removeAttribute('required');

        if (selectionType === 'specific') {
            if (specificDiv) specificDiv.classList.remove('hidden');
        } else if (selectionType === 'any_available') {
            if (anyAvailableInfo) anyAvailableInfo.classList.remove('hidden');
        } else if (selectionType === 'external') {
            if (externalDiv) externalDiv.classList.remove('hidden');
            if (externalInfo) externalInfo.classList.remove('hidden');
            if (externalNameInput) externalNameInput.setAttribute('required', 'required');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        togglePriestOptions();
        initAvailabilityCheck();
    });

    function initAvailabilityCheck() {
        const dateInput = document.getElementById('schedule_date');
        const timeInput = document.getElementById('schedule_time');
        const venueSelect = document.getElementById('venue_select');
        const priestCheckboxes = document.querySelectorAll('input[name="priest_ids[]"]');

        if (dateInput) dateInput.addEventListener('change', checkAvailability);
        if (timeInput) timeInput.addEventListener('change', checkAvailability);
        if (venueSelect) venueSelect.addEventListener('change', checkAvailability);
        priestCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', checkAvailability);
        });
    }

    let availabilityTimeout = null;
    let currentAvailabilityStatus = { available: true };
    
    async function checkAvailability() {
        clearTimeout(availabilityTimeout);
        availabilityTimeout = setTimeout(async () => {
            await performAvailabilityCheck();
        }, 500);
    }

    async function performAvailabilityCheck() {
        const dateInput = document.getElementById('schedule_date');
        const timeInput = document.getElementById('schedule_time');
        const venueSelect = document.getElementById('venue_select');
        const priestSelectionType = document.getElementById('priest_selection_type')?.value;

        const date = dateInput?.value;
        const time = timeInput?.value;
        const venueId = venueSelect?.value;

        if (!date || !time) return;

        let priestId = null;
        if (priestSelectionType === 'specific') {
            const selectedPriest = document.querySelector('input[name="priest_ids[]"]:checked');
            priestId = selectedPriest?.value;
        }

        const actualVenueId = (venueId && venueId !== 'custom') ? venueId : null;

        try {
            const response = await fetch('/api/availability/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    date: date,
                    time: time,
                    priest_id: priestId,
                    venue_id: actualVenueId
                })
            });

            const result = await response.json();

            if (result.success) {
                displayAvailabilityStatus(result);
                await updatePriestAvailabilityUI();
                await updateVenueAvailabilityUI();
            }
        } catch (error) {
            console.error('Availability check failed:', error);
        }
    }

    function displayAvailabilityStatus(result) {
        currentAvailabilityStatus = result;
        document.querySelectorAll('.availability-message').forEach(el => el.remove());

        if (result.available) {
            showAvailabilityMessage('schedule_time', 'This time slot is available!', 'success');
        } else {
            if (!result.priest_available) {
                showAvailabilityMessage('specific_priest_div', 
                    result.messages[0] || 'Priest is not available at this time', 'error');
            }
            if (!result.venue_available) {
                const venueMsg = result.messages.find(m => m.includes('Venue')) || 
                    'Venue is not available at this time';
                showAvailabilityMessage('venue_select', venueMsg, 'error');
            }
            if (result.suggestions && result.suggestions.length > 0) {
                result.suggestions.forEach(suggestion => {
                    if (suggestion.type === 'time') {
                        showAvailabilityMessage('schedule_time', suggestion.message, 'warning');
                    } else if (suggestion.type === 'priest') {
                        showAvailabilityMessage('specific_priest_div', suggestion.message, 'warning');
                    } else if (suggestion.type === 'venue') {
                        showAvailabilityMessage('venue_select', suggestion.message, 'warning');
                    }
                });
            }
        }
    }

    function showAvailabilityMessage(afterElementId, message, type) {
        const targetElement = document.getElementById(afterElementId);
        if (!targetElement) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = 'availability-message flex items-center gap-2 text-xs mt-2 p-2 rounded';
        
        const styles = {
            success: 'bg-green-100 text-green-800 border border-green-200',
            error: 'bg-red-100 text-red-800 border border-red-200',
            warning: 'bg-yellow-100 text-yellow-800 border border-yellow-200'
        };
        const icons = {
            success: '✓',
            error: '⚠️',
            warning: '💡'
        };
        
        messageDiv.className += ' ' + (styles[type] || styles.warning);
        messageDiv.innerHTML = `<span class="font-bold">${icons[type] || icons.warning}</span> <span>${message}</span>`;
        
        if (targetElement.nextSibling) {
             targetElement.parentNode.insertBefore(messageDiv, targetElement.nextSibling);
        } else {
             targetElement.parentNode.appendChild(messageDiv);
        }
    }

    async function updatePriestAvailabilityUI() {
        const dateInput = document.getElementById('schedule_date');
        const timeInput = document.getElementById('schedule_time');

        const date = dateInput?.value;
        const time = timeInput?.value;

        if (!date || !time) return;

        try {
            const response = await fetch('/api/availability/priests', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ date, time })
            });

            const result = await response.json();

            if (result.success && result.priests) {
                result.priests.forEach(priest => {
                    const checkbox = document.querySelector(`input[name="priest_ids[]"][value="${priest.id}"]`);
                    if (checkbox) {
                        const label = checkbox.closest('label');
                        if (label) {
                            if (!priest.available) {
                                label.classList.add('opacity-50', 'bg-gray-100');
                                label.title = 'Not available at this time';
                                
                                let busyBadge = label.querySelector('.busy-badge');
                                if (!busyBadge) {
                                    busyBadge = document.createElement('span');
                                    busyBadge.className = 'busy-badge ml-auto text-[10px] bg-red-500 text-white px-1.5 py-0.5 rounded';
                                    busyBadge.textContent = 'BUSY';
                                    label.appendChild(busyBadge);
                                }
                            } else {
                                label.classList.remove('opacity-50', 'bg-gray-100');
                                label.title = '';
                                const busyBadge = label.querySelector('.busy-badge');
                                if (busyBadge) busyBadge.remove();
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Failed to update priest availability UI:', error);
        }
    }

    async function updateVenueAvailabilityUI() {
        const dateInput = document.getElementById('schedule_date');
        const timeInput = document.getElementById('schedule_time');
        const venueSelect = document.getElementById('venue_select');

        const date = dateInput?.value;
        const time = timeInput?.value;

        if (!date || !time || !venueSelect) return;

        try {
            const response = await fetch('/api/availability/venues', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ date, time })
            });

            const result = await response.json();

            if (result.success && result.venues) {
                result.venues.forEach(venue => {
                    const option = venueSelect.querySelector(`option[value="${venue.id}"]`);
                    if (option) {
                        if (!venue.available) {
                            if (!option.textContent.includes('(BUSY)')) {
                                option.textContent = option.textContent + ' (BUSY)';
                                option.style.color = '#ef4444'; 
                            }
                        } else {
                            option.textContent = option.textContent.replace(' (BUSY)', '');
                            option.style.color = '';
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Failed to update venue availability UI:', error);
        }
    }
</script>

@if ($errors->any() || session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let allErrors = [];
            
            @if ($errors->any())
                const validationErrors = @json($errors->all());
                allErrors = allErrors.concat(validationErrors);
            @endif

            @if (session('error'))
                allErrors.push(@json(session('error')));
            @endif

            if (allErrors.length > 0) {
                if (typeof showValidationErrorModal === 'function') {
                    showValidationErrorModal(allErrors);
                } else {
                    alert(allErrors.join('\n'));
                }
            }
        });
    </script>
@endif

@endsection
