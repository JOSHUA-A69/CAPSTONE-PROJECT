@extends('layouts.app')

@section('content')

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

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
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
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
                        <div class="w-24 h-24 bg-teal-600 rounded-full flex items-center justify-center shadow-lg p-1">
                            <div class="w-full h-full bg-white dark:bg-gray-800 rounded-full flex items-center justify-center p-2">
                                <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-full h-full object-contain" />
                            </div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mb-2">
                        Organization Activity Booking
                    </h2>
                    <p class="text-gray-500 dark:text-gray-400 text-sm max-w-xl mx-auto">
                        Submit your organization's activity proposal for adviser approval. Please ensure all details are accurate.
                    </p>
                </div>
            </div>

            <!-- Help Section (Collapsible) -->
            <div class="border-b border-gray-200 dark:border-gray-700 bg-teal-50 dark:bg-gray-900/50">
                <details class="group p-4" open>
                    <summary class="flex items-center justify-between cursor-pointer list-none text-sm font-medium text-teal-700 dark:text-teal-300">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Need help?
                        </span>
                        <span class="transition group-open:rotate-180">
                            <svg fill="none" class="w-4 h-4" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>
                    <div class="text-xs text-teal-600 dark:text-teal-400 mt-3 ml-7 space-y-1 transition-all duration-300">
                        <p><strong>Required fields are marked with <span class="text-red-500">*</span></strong></p>
                        <ul class="list-disc pl-4 space-y-1 opacity-80">
                            <li>Select your organization from the dropdown list</li>
                            <li>Your request will be sent to your organization's adviser for approval</li>
                            <li>Requests should be submitted at least 7 days before the event</li>
                        </ul>
                    </div>
                </details>
            </div>

            <form method="POST" action="{{ route('requestor.organization-bookings.store') }}" id="organizationBookingForm" novalidate class="p-6 md:p-8 space-y-8">
                @csrf

                <!-- Section 1: Organization Selection -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-teal-500 pl-3">Organization Information</h3>

                    <div class="bg-teal-50 dark:bg-teal-900/20 rounded-xl p-5 border border-teal-100 dark:border-teal-800/30">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Select Organization(s) <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Select one or more organizations involved in this activity, then assign server quantity per organization (maximum of 30 each).</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="organizations-container">
                            @foreach($organizations as $org)
                                <label class="org-card relative flex items-center p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-teal-50 dark:hover:bg-teal-900/10 hover:border-teal-200 dark:hover:border-teal-500/30 transition-all cursor-pointer group shadow-sm" data-org-id="{{ $org->org_id }}" data-old-quantity="{{ old('organization_server_quantities.' . $org->org_id) }}">
                                    <div class="flex items-center gap-3 w-full">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-teal-700 dark:group-hover:text-teal-300">
                                                {{ $org->org_name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                Adviser: {{ $org->adviser ? $org->adviser->full_name : 'No adviser' }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <input type="checkbox" name="organization_ids[]" value="{{ $org->org_id }}"
                                                {{ is_array(old('organization_ids')) && in_array($org->org_id, old('organization_ids')) ? 'checked' : '' }}
                                                class="org-checkbox h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded transition-colors"
                                                onchange="updateOrgServerQuantityInputs()">
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('organization_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('organization_server_quantities') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                        <!-- Server Quantity Per Organization -->
                        <div id="org_quantity_container" class="hidden mt-4 p-4 bg-teal-100 dark:bg-teal-800/30 rounded-xl border border-teal-200 dark:border-teal-700/50">
                            <label class="block text-sm font-medium text-teal-800 dark:text-teal-200 mb-2">
                                Assign Servers Per Organization
                            </label>
                            <p class="text-xs text-teal-700 dark:text-teal-300 mb-3">Select the quantity needed for each chosen organization. Maximum is 30 per organization.</p>
                            <div id="org_quantity_options" class="space-y-3">
                                <!-- Dynamically populated -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Activity Details -->
                <div class="space-y-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-teal-500 pl-3">Activity Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Activity Name -->
                        <div class="col-span-1 md:col-span-4">
                            <label for="activity_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Activity Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="activity_name" id="activity_name" value="{{ old('activity_name') }}" required maxlength="200"
                                placeholder="e.g., Christmas Concert, Youth Retreat, Community Service"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors">
                            <div class="flex justify-between mt-1">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Official event name</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400" id="activity_name_counter">0 / 200</span>
                            </div>
                            @error('activity_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Date -->
                        <div>
                            <label for="requested_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date of Activity <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="requested_date" id="requested_date" value="{{ old('requested_date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must be at least 7 days from today</p>
                            @error('requested_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Time In -->
                        <div>
                            <label for="time_in" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Time In (Start) <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="time_in" id="time_in" value="{{ old('time_in', '08:00') }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors">
                            @error('time_in') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Time Out -->
                        <div>
                            <label for="time_out" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Time Out (End) <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="time_out" id="time_out" value="{{ old('time_out', '17:00') }}" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors">
                            @error('time_out') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                         <!-- Purpose -->
                         <div class="col-span-1 md:col-span-4">
                             <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="md:col-span-3">
                                    <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Purpose <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="purpose" id="purpose" rows="4" maxlength="1000" required placeholder="Describe the purpose and goals of this activity..."
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors">{{ old('purpose') }}</textarea>
                                    <div class="text-right text-xs text-gray-500 dark:text-gray-400 mt-1" id="purpose_counter">0 / 1000</div>
                                    @error('purpose') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="estimated_participants" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Expected Participants
                                    </label>
                                    <input type="number" name="estimated_participants" id="estimated_participants" value="{{ old('estimated_participants') }}" min="1" max="10000" placeholder="e.g., 35"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors">
                                    @error('estimated_participants') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Venue & Requirements -->
                <div class="space-y-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-l-4 border-teal-500 pl-3">Venue & Requirements</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Venue -->
                        <div>
                            <label for="requested_venue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Preferred Venue
                            </label>
                            <input type="text" name="requested_venue" id="requested_venue" value="{{ old('requested_venue') }}" placeholder="e.g., Church Hall, Outdoor Area" maxlength="255"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors">
                            @error('requested_venue') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Special Requirements -->
                        <div class="md:col-span-4">
                             <label for="special_requirements" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Special Requirements
                            </label>
                            <textarea name="special_requirements" id="special_requirements" rows="3" maxlength="1000" placeholder="List any special equipment, setup needs, or accommodations..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors">{{ old('special_requirements') }}</textarea>
                            <div class="text-right text-xs text-gray-500 dark:text-gray-400 mt-1" id="requirements_counter">0 / 1000</div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-8 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        Holy Name University - CREaM Office
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ route('requestor.organization-bookings.index') }}" class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 text-center transition-all">
                            Cancel
                        </a>
                        <button type="submit" id="submitBtn" class="w-full sm:w-auto px-6 py-2.5 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:ring-4 focus:ring-teal-300 dark:focus:ring-teal-800 shadow-md transform transition-all hover:-translate-y-0.5 relative overflow-hidden">
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
    // Character counter
    function updateCharCounter(textareaId, counterId, maxLength) {
        const textarea = document.getElementById(textareaId);
        const counter = document.getElementById(counterId);

        if (!textarea || !counter) return;

        const updateCount = () => {
            const length = textarea.value.length;
            counter.textContent = `${length} / ${maxLength} characters`;

            counter.classList.remove('text-amber-500', 'text-red-500');
            if (length > maxLength * 0.9) {
                counter.classList.add('text-red-500');
            } else if (length > maxLength * 0.75) {
                counter.classList.add('text-amber-500');
            }
        };

        textarea.addEventListener('input', updateCount);
        updateCount();
    }

    // Organization details toggle
    function initOrgSelector() {
        // Initialize quantity inputs for selected organizations
        updateOrgServerQuantityInputs();
    }

    // Show server quantity selector for each checked organization
    function updateOrgServerQuantityInputs() {
        const checkboxes = document.querySelectorAll('.org-checkbox:checked');
        const container = document.getElementById('org_quantity_container');
        const optionsDiv = document.getElementById('org_quantity_options');

        const currentValues = {};
        document.querySelectorAll('select[name^="organization_server_quantities["]').forEach(select => {
            const match = select.name.match(/organization_server_quantities\[(.+)\]/);
            if (match && match[1]) {
                currentValues[match[1]] = select.value;
            }
        });

        if (checkboxes.length === 0) {
            container.classList.add('hidden');
            optionsDiv.innerHTML = '';
            return;
        }

        container.classList.remove('hidden');

        let html = '';
        checkboxes.forEach((checkbox) => {
            const orgId = checkbox.value;
            const card = checkbox.closest('.org-card');
            const orgName = card.querySelector('p.font-semibold').textContent.trim();
            const oldQuantity = card.dataset.oldQuantity || '';
            const selectedValue = currentValues[orgId] || oldQuantity;

            html += `
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-teal-200 dark:border-teal-700/40 p-3">
                    <label for="org-qty-${orgId}" class="block text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">${escapeHtmlForOrg(orgName)}</label>
                    <select id="org-qty-${orgId}" name="organization_server_quantities[${orgId}]" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-colors" required>
                        <option value="">Select quantity (max 30)</option>
                        ${Array.from({ length: 30 }, (_, i) => {
                            const quantity = String(i + 1);
                            const selected = String(selectedValue) === quantity ? 'selected' : '';
                            return `<option value="${quantity}" ${selected}>${quantity} server${quantity === '1' ? '' : 's'}</option>`;
                        }).join('')}
                    </select>
                </div>
            `;
        });

        optionsDiv.innerHTML = html;
    }

    function escapeHtmlForOrg(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function parseTimeToMinutes(timeValue) {
        if (!timeValue) return null;

        const normalized = String(timeValue).trim().toLowerCase();

        // Handles native input[type=time] values like HH:mm or HH:mm:ss
        const twentyFourHourMatch = normalized.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (twentyFourHourMatch) {
            const hours = Number(twentyFourHourMatch[1]);
            const minutes = Number(twentyFourHourMatch[2]);
            return (hours * 60) + minutes;
        }

        // Handles fallback values like h:mm am/pm
        const twelveHourMatch = normalized.match(/^(\d{1,2}):(\d{2})\s*(am|pm)$/);
        if (twelveHourMatch) {
            let hours = Number(twelveHourMatch[1]);
            const minutes = Number(twelveHourMatch[2]);
            const period = twelveHourMatch[3];
            if (period === 'pm' && hours < 12) hours += 12;
            if (period === 'am' && hours === 12) hours = 0;
            return (hours * 60) + minutes;
        }

        return null;
    }

    // Form validation
    function validateForm() {
        const form = document.getElementById('organizationBookingForm');
        let isValid = true;
        let errorMessages = [];

        // Check at least one organization is selected
        const orgCheckboxes = document.querySelectorAll('.org-checkbox:checked');
        if (orgCheckboxes.length === 0) {
            errorMessages.push('Please select at least one organization');
            isValid = false;

            // Highlight org container
            document.getElementById('organizations-container').classList.add('ring-2', 'ring-red-500', 'rounded-xl');
        } else {
            document.getElementById('organizations-container').classList.remove('ring-2', 'ring-red-500', 'rounded-xl');

            orgCheckboxes.forEach(checkbox => {
                const quantityField = document.querySelector(`select[name="organization_server_quantities[${checkbox.value}]"]`);
                if (!quantityField || !quantityField.value) {
                    isValid = false;
                    errorMessages.push('Please select server quantity for every selected organization.');
                    if (quantityField) {
                        quantityField.classList.add('border-red-500');
                    }
                    return;
                }

                const quantity = Number(quantityField.value);
                if (!Number.isInteger(quantity) || quantity < 1 || quantity > 30) {
                    isValid = false;
                    errorMessages.push('Server quantity per organization must be between 1 and 30.');
                    quantityField.classList.add('border-red-500');
                } else {
                    quantityField.classList.remove('border-red-500');
                }
            });
        }

        // Check required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;

                let label = getFieldLabel(field);
                if (label && !errorMessages.includes(label)) {
                    errorMessages.push(label);
                }
            } else {
                field.classList.remove('border-red-500');
            }
        });

         // Validate date (must be at least 7 days from now)
        const dateInput = document.getElementById('requested_date');
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

        // Validate time range (time out must be after time in)
        const timeInInput = document.getElementById('time_in');
        const timeOutInput = document.getElementById('time_out');
        if (timeInInput && timeOutInput && timeInInput.value && timeOutInput.value) {
            const timeInMinutes = parseTimeToMinutes(timeInInput.value);
            const timeOutMinutes = parseTimeToMinutes(timeOutInput.value);

            if (timeInMinutes === null || timeOutMinutes === null || timeOutMinutes <= timeInMinutes) {
                timeInInput.classList.add('border-red-500');
                timeOutInput.classList.add('border-red-500');
                errorMessages.push('Time Out must be later than Time In.');
                isValid = false;
            } else {
                timeInInput.classList.remove('border-red-500');
                timeOutInput.classList.remove('border-red-500');
            }
        }

        if (!isValid) {
            showValidationErrorModal(errorMessages);
        }

        return isValid;
    }

    function getFieldLabel(field) {
        const fieldLabels = {
            'organization_id': 'Organization',
            'activity_name': 'Activity Name',
            'requested_date': 'Date of Activity',
            'time_in': 'Time In (Start)',
            'time_out': 'Time Out (End)',
            'purpose': 'Purpose',
            'estimated_participants': 'Expected Participants',
            'requested_venue': 'Preferred Venue',
            'special_requirements': 'Special Requirements'
        };

        if (fieldLabels[field.id]) return fieldLabels[field.id];
        return field.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function showValidationErrorModal(errors) {
        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const existingModal = document.getElementById('validationErrorModal');
        if (existingModal) existingModal.remove();

        const uniqueErrors = [...new Set(errors)];
        const modalHtml = `
            <div id="validationErrorModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 animate-fade-in" onclick="if(event.target === this) closeValidationErrorModal()">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden animate-slide-up">
                    <div class="bg-red-50 dark:bg-red-900/30 p-6 text-center border-b border-red-100 dark:border-red-800/50">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50 mb-4">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-red-900 dark:text-red-100">Action Required</h3>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">Please fill in the required fields</p>
                    </div>
                    <div class="p-6">
                        <ul class="max-h-48 overflow-y-auto space-y-2">
                             ${uniqueErrors.map(err => `<li class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"><span class="text-red-500">•</span>${escapeHtml(err)}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50">
                        <button onclick="closeValidationErrorModal()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-3 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:text-sm">OK, I'll fix it</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        resetSubmitButton();
    }

    function closeValidationErrorModal() {
        const modal = document.getElementById('validationErrorModal');
        if (modal) modal.remove();

        const firstError = document.querySelector('.border-red-500');
        if (firstError) {
             firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
             setTimeout(() => firstError.focus(), 300);
        }
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

    document.addEventListener('DOMContentLoaded', function() {
        initOrgSelector();
        updateCharCounter('activity_name', 'activity_name_counter', 200);
        updateCharCounter('purpose', 'purpose_counter', 1000);
        updateCharCounter('special_requirements', 'requirements_counter', 1000);

        const form = document.getElementById('organizationBookingForm');
        const timeInInput = document.getElementById('time_in');
        const timeOutInput = document.getElementById('time_out');

        if (timeInInput && timeOutInput) {
            const enforceTimeOrdering = () => {
                const inMinutes = parseTimeToMinutes(timeInInput.value);
                const outMinutes = parseTimeToMinutes(timeOutInput.value);
                if (inMinutes !== null && outMinutes !== null && outMinutes <= inMinutes) {
                    timeOutInput.setCustomValidity('Time Out must be later than Time In.');
                } else {
                    timeOutInput.setCustomValidity('');
                }
            };

            timeInInput.addEventListener('change', enforceTimeOrdering);
            timeOutInput.addEventListener('change', enforceTimeOrdering);
            enforceTimeOrdering();
        }

        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }

            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoader = document.getElementById('submitLoader');
            const loadingOverlay = document.getElementById('loadingOverlay');

            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoader.style.display = 'inline';
            if (loadingOverlay) loadingOverlay.classList.remove('hidden');
        });
    });
</script>

@endsection
