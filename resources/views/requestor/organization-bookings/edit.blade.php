@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('requestor.organization-bookings.index') }}" 
               class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Requests
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-4">Edit Organization Booking Request</h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Update your organization booking request details</p>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('requestor.organization-bookings.update', $organizationBookingRequest) }}" method="POST" id="organization-booking-form">
            @csrf
            @method('PUT')

            <!-- Organization Selection -->
            <div class="mb-6">
                <label for="organization_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Organization <span class="text-red-500">*</span>
                </label>
                <select id="organization_id" 
                        name="organization_id" 
                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('organization_id') border-red-500 @enderror"
                        required>
                    <option value="">-- Select an organization --</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->org_id }}" 
                                data-adviser="{{ $org->adviser ? $org->adviser->full_name : 'No adviser assigned' }}"
                                data-desc="{{ $org->org_desc }}"
                                {{ old('organization_id', $organizationBookingRequest->organization_id) == $org->org_id ? 'selected' : '' }}>
                            {{ $org->org_name }}
                        </option>
                    @endforeach
                </select>
                @error('organization_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                
                <!-- Organization Info Display -->
                <div id="org-info" class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-md hidden">
                    <div class="text-sm">
                        <div id="org-description" class="text-gray-600 dark:text-gray-300 mb-2"></div>
                        <div id="org-adviser" class="text-gray-500 dark:text-gray-400"></div>
                    </div>
                </div>
            </div>

            <!-- Activity Name -->
            <div class="mb-6">
                <label for="activity_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Activity Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="activity_name" 
                       name="activity_name" 
                       value="{{ old('activity_name', $organizationBookingRequest->activity_name) }}"
                       placeholder="e.g., Christmas Concert, Youth Retreat, Community Service"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('activity_name') border-red-500 @enderror"
                       maxlength="255"
                       required>
                @error('activity_name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Purpose -->
            <div class="mb-6">
                <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Purpose <span class="text-red-500">*</span>
                </label>
                <textarea id="purpose" 
                          name="purpose" 
                          rows="4"
                          placeholder="Describe the purpose and goals of this activity..."
                          class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('purpose') border-red-500 @enderror"
                          maxlength="1000"
                          required>{{ old('purpose', $organizationBookingRequest->purpose) }}</textarea>
                <div class="mt-1 flex justify-between">
                    @error('purpose')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Explain why this activity is important and what you hope to achieve</p>
                    @enderror
                    <p class="text-sm text-gray-400" id="purpose-counter">0/1000</p>
                </div>
            </div>

            <!-- Requested Date & Time -->
            <div class="mb-6">
                <label for="requested_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Requested Date & Time <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" 
                       id="requested_date" 
                       name="requested_date" 
                       value="{{ old('requested_date', $organizationBookingRequest->requested_date->format('Y-m-d\TH:i')) }}"
                       min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                       class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('requested_date') border-red-500 @enderror"
                       required>
                @error('requested_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @else
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select your preferred date and time</p>
                @enderror
            </div>

            <!-- Two Column Layout for Additional Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Requested Venue -->
                <div>
                    <label for="requested_venue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Preferred Venue
                    </label>
                    <input type="text" 
                           id="requested_venue" 
                           name="requested_venue" 
                           value="{{ old('requested_venue', $organizationBookingRequest->requested_venue) }}"
                           placeholder="e.g., Church Hall, Outdoor Area"
                           class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('requested_venue') border-red-500 @enderror"
                           maxlength="255">
                    @error('requested_venue')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @else
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Optional: Specify if you have a venue preference</p>
                    @enderror
                </div>

                <!-- Estimated Participants -->
                <div>
                    <label for="estimated_participants" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Estimated Participants
                    </label>
                    <input type="number" 
                           id="estimated_participants" 
                           name="estimated_participants" 
                           value="{{ old('estimated_participants', $organizationBookingRequest->estimated_participants) }}"
                           placeholder="50"
                           min="1"
                           max="10000"
                           class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('estimated_participants') border-red-500 @enderror">
                    @error('estimated_participants')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @else
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Optional: Help us prepare appropriate arrangements</p>
                    @enderror
                </div>
            </div>

            <!-- Special Requirements -->
            <div class="mb-6">
                <label for="special_requirements" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Special Requirements
                </label>
                <textarea id="special_requirements" 
                          name="special_requirements" 
                          rows="3"
                          placeholder="Any special equipment, setup, or accommodation needs..."
                          class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('special_requirements') border-red-500 @enderror"
                          maxlength="1000">{{ old('special_requirements', $organizationBookingRequest->special_requirements) }}</textarea>
                <div class="mt-1 flex justify-between">
                    @error('special_requirements')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Optional: List any special needs or requirements</p>
                    @enderror
                    <p class="text-sm text-gray-400" id="requirements-counter">0/1000</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('requestor.organization-bookings.index') }}" 
                   class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Request
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Organization selection handler
    const orgSelect = document.getElementById('organization_id');
    const orgInfo = document.getElementById('org-info');
    const orgDescription = document.getElementById('org-description');
    const orgAdviser = document.getElementById('org-adviser');

    orgSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value && selectedOption.dataset.desc) {
            orgDescription.textContent = selectedOption.dataset.desc;
            orgAdviser.textContent = 'Adviser: ' + selectedOption.dataset.adviser;
            orgInfo.classList.remove('hidden');
        } else {
            orgInfo.classList.add('hidden');
        }
    });

    // Character counters
    function setupCounter(textareaId, counterId) {
        const textarea = document.getElementById(textareaId);
        const counter = document.getElementById(counterId);
        
        if (textarea && counter) {
            function updateCounter() {
                const length = textarea.value.length;
                const maxLength = textarea.maxLength;
                counter.textContent = `${length}/${maxLength}`;
                
                if (length > maxLength * 0.9) {
                    counter.classList.add('text-red-500');
                } else {
                    counter.classList.remove('text-red-500');
                }
            }
            
            textarea.addEventListener('input', updateCounter);
            updateCounter(); // Initial count
        }
    }

    setupCounter('purpose', 'purpose-counter');
    setupCounter('special_requirements', 'requirements-counter');

    // Trigger initial organization info display if value exists
    if (orgSelect.value) {
        orgSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
