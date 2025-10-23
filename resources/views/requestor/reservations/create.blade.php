@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Spiritual Activity Request Form</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Holy Name University - CREaM Office</p>
    </div>

    <form method="POST" action="{{ route('requestor.reservations.store') }}" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        @csrf

        <!-- Activity Information Section -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Activity Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Name of Activity <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="activity_name" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('activity_name') }}" required placeholder="e.g., Send-off Mass for BSET Board Takers">
                    @error('activity_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Date of Activity <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="schedule_date" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('schedule_date') }}" required>
                    @error('schedule_date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Time <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="schedule_time" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('schedule_time', '08:00') }}" required>
                    @error('schedule_time') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Expected Number of Participants
                    </label>
                    <input type="number" name="participants_count" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('participants_count') }}" min="1" placeholder="e.g., 35">
                    @error('participants_count') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Theme
                </label>
                <textarea name="theme" class="form-textarea w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" rows="2" placeholder="e.g., Empowered by Faith, Guided to Serve: A Send-Off for our Future B&TS">{{ old('theme') }}</textarea>
                @error('theme') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Service Type <span class="text-red-500">*</span>
                    </label>
                    <select name="service_id" class="form-select w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                        <option value="">-- Select Service --</option>
                        @foreach($services as $s)
                            <option value="{{ $s->service_id }}" @if(old('service_id')==$s->service_id) selected @endif>{{ $s->service_name }}</option>
                        @endforeach
                    </select>
                    @error('service_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Venue <span class="text-red-500">*</span>
                    </label>
                    <select name="venue_id" id="venue_select" class="form-select w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required onchange="toggleCustomVenue()">
                        <option value="">-- Select Venue --</option>
                        @foreach($venues as $v)
                            <option value="{{ $v->venue_id }}" @if(old('venue_id')==$v->venue_id) selected @endif>{{ $v->name }}</option>
                        @endforeach
                        <option value="custom" @if(old('venue_id')=='custom') selected @endif>📍 Custom Location (Home/Other)</option>
                    </select>
                    @error('venue_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Custom Venue Field (shows when "Custom Location" is selected) -->
            <div id="custom_venue_container" class="mb-4" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Custom Venue Location <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="custom_venue"
                       id="custom_venue_input"
                       class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                       value="{{ old('custom_venue') }}"
                       placeholder="e.g., Barangay San Roque Chapel, Private Residence at 123 Main St.">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Please provide the complete address or location name
                </p>
                @error('custom_venue') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Preferred Presider/Priest <span class="text-red-500">*</span>
                </label>
                <select name="officiant_id" class="form-select w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
                    <option value="">-- Select Priest/Presider --</option>
                    @foreach($priests as $priest)
                        <option value="{{ $priest->id }}" @if(old('officiant_id')==$priest->id) selected @endif>
                            {{ $priest->full_name }} @if($priest->email) ({{ $priest->email }}) @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    The selected priest will be notified and asked to confirm availability. If unavailable, CREaM will assign another priest.
                </p>
                @error('officiant_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Requesting Office/Group Section -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requesting Office/Group</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Organization
                </label>
                <select name="org_id" class="form-select w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <option value="">-- None / Individual --</option>
                    @foreach($organizations as $o)
                        <option value="{{ $o->org_id }}" @if(old('org_id')==$o->org_id) selected @endif>{{ $o->org_name }}</option>
                    @endforeach
                </select>
                @error('org_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Ministry Volunteers Section -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ministry Volunteers</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Please provide names of volunteers for each role. Write "N/A" if not applicable.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Commentator
                    </label>
                    <input type="text" name="commentator" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('commentator') }}" placeholder="Name of commentator">
                    @error('commentator') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Servers
                    </label>
                    <input type="text" name="servers" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('servers') }}" placeholder="Names of servers (comma-separated)">
                    @error('servers') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Readers
                    </label>
                    <input type="text" name="readers" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('readers') }}" placeholder="Names of readers (comma-separated)">
                    @error('readers') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Choir
                    </label>
                    <input type="text" name="choir" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('choir') }}" placeholder="Names of choir members">
                    @error('choir') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Psalmist
                    </label>
                    <input type="text" name="psalmist" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('psalmist') }}" placeholder="Name of psalmist">
                    @error('psalmist') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Leader for Prayer of the Faithful
                    </label>
                    <input type="text" name="prayer_leader" class="form-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" value="{{ old('prayer_leader') }}" placeholder="Name or group">
                    @error('prayer_leader') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Additional Information Section -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Remarks/Other Requests</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Additional Details or Special Requests
                </label>
                <textarea name="details" class="form-textarea w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" rows="4" placeholder="Write N/A if not applicable">{{ old('details') }}</textarea>
                @error('details') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center gap-3 pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                Submit Request
            </button>
            <a href="{{ route('requestor.reservations.index') }}" class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg shadow-sm transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Toggle custom venue field visibility
    function toggleCustomVenue() {
        const venueSelect = document.getElementById('venue_select');
        const customContainer = document.getElementById('custom_venue_container');
        const customInput = document.getElementById('custom_venue_input');
        
        if (venueSelect.value === 'custom') {
            customContainer.style.display = 'block';
            customInput.required = true;
        } else {
            customContainer.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    }
    
    // Check on page load if "custom" was previously selected (for validation errors)
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomVenue();
    });
</script>

@endsection
