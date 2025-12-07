@extends('layouts.app')

@section('content')
<style>
    @media print {
        .no-print { display: none; }
    }

    .form-container {
        max-width: 900px;
        margin: 2rem auto;
        background: #ffffff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        overflow: hidden;
    }

    .dark .form-container {
        background: #1f2937;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Validation States */
    .form-table input.is-invalid,
    .form-table select.is-invalid,
    .form-table textarea.is-invalid {
        background: #fef2f2;
        border-bottom: 2px solid #dc2626 !important;
    }

    .dark .form-table input.is-invalid,
    .dark .form-table select.is-invalid,
    .dark .form-table textarea.is-invalid {
        background: #7f1d1d;
        border-bottom: 2px solid #ef4444 !important;
    }

    .form-table input.is-valid,
    .form-table select.is-valid,
    .form-table textarea.is-valid {
        background: #f0fdf4;
        border-bottom: 2px solid #16a34a !important;
    }

    .dark .form-table input.is-valid,
    .dark .form-table select.is-valid,
    .dark .form-table textarea.is-valid {
        background: #14532d;
        border-bottom: 2px solid #22c55e !important;
    }

    .error-message {
        color: #dc2626;
        font-size: 12px;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .success-indicator {
        color: #16a34a;
        font-size: 16px;
        margin-left: 4px;
    }

    .required-indicator {
        color: #dc2626;
        font-weight: bold;
        margin-left: 2px;
    }

    .char-counter {
        font-size: 12px;
        color: #6b7280;
        text-align: right;
        margin-top: 2px;
    }

    .char-counter.warning {
        color: #d97706;
    }

    .char-counter.danger {
        color: #dc2626;
    }

    .help-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        font-size: 12px;
        font-weight: bold;
        margin-left: 4px;
        cursor: help;
        border: none;
        transition: all 0.2s ease;
    }

    .help-icon:hover {
        background: #059669;
        color: white;
    }

    .tooltip {
        position: relative;
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: 220px;
        background-color: #1f2937;
        color: #fff;
        text-align: left;
        border-radius: 6px;
        padding: 8px 10px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -110px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 12px;
        line-height: 1.5;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }

    .tooltip .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1f2937 transparent transparent transparent;
    }

    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }

    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .loading-overlay.active {
        display: flex;
    }

    .spinner {
        border: 4px solid #f3f4f6;
        border-top: 4px solid #059669;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .form-header {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 18px;
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        color: #ffffff;
        text-transform: uppercase;
        border-bottom: 3px solid #059669;
    }

    .dark .form-header {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
        border-bottom: 3px solid #10b981;
    }

    .form-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 14px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.5;
    }

    .form-table td {
        border: 1px solid #d1d5db;
        padding: 10px 12px;
        vertical-align: top;
        background: #ffffff;
    }

    .dark .form-table td {
        border: 1px solid #374151;
        background: #1f2937;
    }

    .form-table label {
        font-weight: 600;
        display: inline;
        margin-right: 6px;
        color: #374151;
        font-size: 13px;
    }

    .dark .form-table label {
        color: #e5e7eb;
    }

    .form-table input[type="text"],
    .form-table input[type="date"],
    .form-table input[type="time"],
    .form-table input[type="number"],
    .form-table select,
    .form-table textarea {
        border: none;
        outline: none;
        background: transparent;
        width: 100%;
        font-size: 14px;
        padding: 4px 0;
        font-family: inherit;
        color: #1f2937;
        transition: background-color 0.15s ease;
    }

    .dark .form-table input[type="text"],
    .dark .form-table input[type="date"],
    .dark .form-table input[type="time"],
    .dark .form-table input[type="number"],
    .dark .form-table select,
    .dark .form-table textarea {
        color: #f3f4f6;
    }

    /* Dark mode calendar and clock icons */
    .dark .form-table input[type="date"]::-webkit-calendar-picker-indicator,
    .dark .form-table input[type="time"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }

    /* For Firefox */
    .dark .form-table input[type="date"],
    .dark .form-table input[type="time"] {
        color-scheme: dark;
    }

    .form-table input[type="text"]:focus,
    .form-table input[type="date"]:focus,
    .form-table input[type="time"]:focus,
    .form-table input[type="number"]:focus,
    .form-table select:focus,
    .form-table textarea:focus {
        background: #f9fafb;
        border-radius: 2px;
    }

    .dark .form-table input[type="text"]:focus,
    .dark .form-table input[type="date"]:focus,
    .dark .form-table input[type="time"]:focus,
    .dark .form-table input[type="number"]:focus,
    .dark .form-table select:focus,
    .dark .form-table textarea:focus {
        background: #374151;
        border-radius: 2px;
    }

    .form-table textarea {
        resize: none;
        line-height: 1.5;
    }

    .form-table select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 4px center;
        background-repeat: no-repeat;
        background-size: 1.2em;
        padding-right: 1.5em;
    }

    .section-header {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        font-weight: 700;
        text-align: left;
        color: #1f2937;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px !important;
    }

    .dark .section-header {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        color: #f3f4f6;
    }

    .form-note {
        font-style: italic;
        font-size: 12px;
        padding: 10px !important;
        background: #ecfdf5;
        border-top: 2px solid #34d399;
        color: #065f46;
    }

    .dark .form-note {
        background: #064e3b;
        border-top: 2px solid #10b981;
        color: #a7f3d0;
    }

    .form-actions {
        padding: 20px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dark .form-actions {
        background: #111827;
        border-top: 1px solid #374151;
    }

    .form-actions .office-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dark .form-actions .office-label {
        color: #9ca3af;
    }

    .btn-group {
        display: flex;
        gap: 12px;
    }

    .btn {
        padding: 12px 28px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
        border: none;
        letter-spacing: 0.3px;
    }

    .btn-cancel {
        background: #ffffff;
        color: #374151;
        border: 1.5px solid #d1d5db;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .btn-cancel:hover {
        background: #f9fafb;
        border-color: #9ca3af;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .btn-submit {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #047857 0%, #065f46 100%);
        box-shadow: 0 4px 8px rgba(5, 150, 105, 0.4);
        transform: translateY(-1px);
    }

    .dark .btn-cancel {
        background: #374151;
        color: #f3f4f6;
        border-color: #4b5563;
    }

    .dark .btn-cancel:hover {
        background: #4b5563;
        border-color: #6b7280;
    }

    .org-info-box {
        margin-top: 8px;
        padding: 10px;
        background: #f0fdf4;
        border-radius: 6px;
        border-left: 3px solid #10b981;
    }

    .dark .org-info-box {
        background: #064e3b;
        border-left: 3px solid #34d399;
    }

    .org-info-box .org-desc {
        font-size: 12px;
        color: #065f46;
        margin-bottom: 4px;
    }

    .dark .org-info-box .org-desc {
        color: #a7f3d0;
    }

    .org-info-box .org-adviser {
        font-size: 11px;
        color: #047857;
        font-weight: 500;
    }

    .dark .org-info-box .org-adviser {
        color: #6ee7b7;
    }

    /* Screen reader only */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>



<div class="form-container">
    <!-- Validation Errors -->
    @if ($errors->any())
    <div class="no-print" style="background: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 0;">
        <div style="display: flex; align-items: start; gap: 12px;">
            <svg style="width: 24px; height: 24px; color: #dc2626; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div style="flex: 1;">
                <h3 style="font-weight: 600; color: #991b1b; margin-bottom: 8px; font-size: 14px;">Please correct the following errors:</h3>
                <ul style="list-style: disc; margin-left: 20px; color: #dc2626; font-size: 12px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('requestor.organization-bookings.store') }}" id="organizationBookingForm" novalidate>
        @csrf

        <!-- Form Header -->
        <div class="form-header">
            🏢 Organization Booking Request Form
        </div>

        <!-- Help Section -->
        <div class="no-print" style="background: #ecfdf5; border-bottom: 1px solid #a7f3d0; padding: 12px 16px;">
            <details style="cursor: pointer;">
                <summary style="font-weight: 600; font-size: 12px; color: #047857; user-select: none;">
                    📖 Need help filling this form? Click here
                </summary>
                <div style="margin-top: 8px; font-size: 11px; color: #065f46; line-height: 1.6;">
                    <p><strong>Required fields are marked with <span style="color: #dc2626;">*</span></strong></p>
                    <ul style="margin: 8px 0 0 20px; list-style: disc;">
                        <li>Select your organization from the dropdown list</li>
                        <li>Your request will be sent to your organization's adviser for approval</li>
                        <li>Provide complete and accurate information about your activity</li>
                        <li>Requests should be submitted at least 7 days before the event</li>
                    </ul>
                </div>
            </details>
        </div>

        <style>
            .dark .no-print[style*="background: #ecfdf5"] {
                background: #064e3b !important;
                border-bottom: 1px solid #10b981 !important;
            }
            .dark .no-print[style*="background: #ecfdf5"] summary {
                color: #6ee7b7 !important;
            }
            .dark .no-print[style*="background: #ecfdf5"] div {
                color: #a7f3d0 !important;
            }
        </style>

        <!-- Form Table -->
        <table class="form-table">
            <!-- Organization Selection Section -->
            <tr>
                <td colspan="2" class="section-header">
                    📋 Organization Information
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label for="organization_id">
                        Select Organization<span class="required-indicator" aria-label="required">*</span>
                        <span class="tooltip help-icon" role="tooltip">
                            ?
                            <span class="tooltiptext">Choose your organization from the list. Your adviser will review and approve this request.</span>
                        </span>
                    </label>
                    <select
                        name="organization_id"
                        id="organization_id"
                        required
                        aria-required="true"
                        class="@error('organization_id') is-invalid @enderror"
                        @error('organization_id') aria-invalid="true" @enderror
                    >
                        <option value="">-- Select an organization --</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->org_id }}" 
                                    data-adviser="{{ $org->adviser ? $org->adviser->full_name : 'No adviser assigned' }}"
                                    data-desc="{{ $org->org_desc }}"
                                    {{ old('organization_id') == $org->org_id ? 'selected' : '' }}>
                                {{ $org->org_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('organization_id')
                        <div class="error-message" role="alert">⚠️ {{ $message }}</div>
                    @enderror
                    
                    <!-- Organization Info Display -->
                    <div id="org-info" class="org-info-box hidden">
                        <div id="org-description" class="org-desc"></div>
                        <div id="org-adviser" class="org-adviser"></div>
                    </div>
                </td>
            </tr>

            <!-- Activity Details Section -->
            <tr>
                <td colspan="2" class="section-header">
                    📝 Activity Details
                </td>
            </tr>
            <tr>
                <!-- Activity Name (60% width) -->
                <td style="width: 60%;">
                    <label for="activity_name">
                        Activity Name<span class="required-indicator" aria-label="required">*</span>
                        <span class="tooltip help-icon" role="tooltip">
                            ?
                            <span class="tooltiptext">Enter the official name of your organization's activity or event</span>
                        </span>
                    </label>
                    <input
                        type="text"
                        name="activity_name"
                        id="activity_name"
                        value="{{ old('activity_name') }}"
                        required
                        aria-required="true"
                        maxlength="200"
                        placeholder="e.g., Christmas Concert, Youth Retreat, Community Service"
                        class="@error('activity_name') is-invalid @enderror"
                        @error('activity_name') aria-invalid="true" @enderror
                    >
                    <div class="char-counter" id="activity_name_counter" aria-live="polite">0 / 200 characters</div>
                    @error('activity_name')
                        <div class="error-message" role="alert">⚠️ {{ $message }}</div>
                    @enderror
                </td>
                <!-- Date & Time (40% width) -->
                <td style="width: 40%;">
                    <div style="margin-bottom: 8px;">
                        <label for="requested_date">
                            Date of Activity<span class="required-indicator" aria-label="required">*</span>
                            <span class="tooltip help-icon" role="tooltip">
                                ?
                                <span class="tooltiptext">Select the date of your event. Should be at least 7 days from today.</span>
                            </span>
                        </label>
                        <input
                            type="date"
                            name="requested_date"
                            id="requested_date"
                            value="{{ old('requested_date') }}"
                            required
                            aria-required="true"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            class="@error('requested_date') is-invalid @enderror"
                            @error('requested_date') aria-invalid="true" @enderror
                        >
                        @error('requested_date')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label for="requested_time">
                            Time<span class="required-indicator">*</span>
                        </label>
                        <input
                            type="time"
                            name="requested_time"
                            id="requested_time"
                            value="{{ old('requested_time', '08:00') }}"
                            required
                            class="@error('requested_time') is-invalid @enderror"
                        >
                        @error('requested_time')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>
                </td>
            </tr>

            <tr>
                <!-- Purpose (60% width) -->
                <td style="width: 60%;">
                    <label for="purpose">
                        Purpose<span class="required-indicator" aria-label="required">*</span>
                        <span class="tooltip help-icon" role="tooltip">
                            ?
                            <span class="tooltiptext">Describe the purpose and goals of this activity. Explain why it's important for your organization.</span>
                        </span>
                    </label>
                    <textarea
                        name="purpose"
                        id="purpose"
                        rows="4"
                        maxlength="1000"
                        placeholder="Describe the purpose and goals of this activity..."
                        required
                        class="@error('purpose') is-invalid @enderror"
                    >{{ old('purpose') }}</textarea>
                    <div class="char-counter" id="purpose_counter" aria-live="polite">0 / 1000 characters</div>
                    @error('purpose')
                        <div class="error-message" role="alert">⚠️ {{ $message }}</div>
                    @enderror
                </td>
                <!-- Participants (40% width) -->
                <td style="width: 40%;">
                    <label for="estimated_participants">
                        Expected Number of Participants
                        <span class="tooltip help-icon" role="tooltip">
                            ?
                            <span class="tooltiptext">Estimated number of attendees for your event. This helps with venue and resource planning.</span>
                        </span>
                    </label>
                    <input
                        type="number"
                        name="estimated_participants"
                        id="estimated_participants"
                        value="{{ old('estimated_participants') }}"
                        placeholder="e.g., 35"
                        min="1"
                        max="10000"
                        class="@error('estimated_participants') is-invalid @enderror"
                    >
                    @error('estimated_participants')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <!-- Venue & Requirements Section -->
            <tr>
                <td colspan="2" class="section-header">
                    📍 Venue & Requirements
                </td>
            </tr>
            <tr>
                <!-- Venue (50% width) -->
                <td style="width: 50%;">
                    <label for="requested_venue">
                        Preferred Venue
                        <span class="tooltip help-icon" role="tooltip">
                            ?
                            <span class="tooltiptext">Specify your preferred location for the activity (e.g., Church Hall, Outdoor Area, Conference Room)</span>
                        </span>
                    </label>
                    <input
                        type="text"
                        name="requested_venue"
                        id="requested_venue"
                        value="{{ old('requested_venue') }}"
                        placeholder="e.g., Church Hall, Outdoor Area"
                        maxlength="255"
                        class="@error('requested_venue') is-invalid @enderror"
                    >
                    @error('requested_venue')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
                <!-- Special Requirements (50% width) -->
                <td style="width: 50%;">
                    <label for="special_requirements">
                        Special Requirements
                        <span class="tooltip help-icon" role="tooltip">
                            ?
                            <span class="tooltiptext">List any special equipment, setup needs, or accommodations required for your activity</span>
                        </span>
                    </label>
                    <textarea
                        name="special_requirements"
                        id="special_requirements"
                        rows="3"
                        maxlength="1000"
                        placeholder="Any special equipment, setup, or accommodation needs..."
                        class="@error('special_requirements') is-invalid @enderror"
                    >{{ old('special_requirements') }}</textarea>
                    <div class="char-counter" id="requirements_counter" aria-live="polite">0 / 1000 characters</div>
                    @error('special_requirements')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <!-- Note -->
            <tr>
                <td colspan="2" class="form-note">
                    <strong>Note:</strong> Your request will be reviewed by your organization's adviser. You will receive a notification once it has been approved or if additional information is needed.
                </td>
            </tr>
        </table>

        <!-- Form Actions -->
        <div class="form-actions">
            <span class="office-label">Campus Ministry Office</span>
            <div class="btn-group">
                <a href="{{ route('requestor.organization-bookings.index') }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-submit" id="submitBtn">
                    📤 Submit Request
                </button>
            </div>
        </div>
    </form>
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
        
        if (this.value) {
            const desc = selectedOption.dataset.desc || 'No description available';
            const adviser = selectedOption.dataset.adviser || 'No adviser assigned';
            orgDescription.textContent = desc;
            orgAdviser.innerHTML = '<strong>Adviser:</strong> ' + adviser;
            orgInfo.classList.remove('hidden');
        } else {
            orgInfo.classList.add('hidden');
        }
    });

    // Character counter function
    function setupCharCounter(inputId, counterId, maxLength) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        
        if (input && counter) {
            function updateCounter() {
                const length = input.value.length;
                counter.textContent = `${length} / ${maxLength} characters`;
                
                if (length >= maxLength * 0.9) {
                    counter.classList.add('danger');
                    counter.classList.remove('warning');
                } else if (length >= maxLength * 0.7) {
                    counter.classList.add('warning');
                    counter.classList.remove('danger');
                } else {
                    counter.classList.remove('warning', 'danger');
                }
            }
            
            input.addEventListener('input', updateCounter);
            updateCounter(); // Initial count
        }
    }

    // Setup character counters
    setupCharCounter('activity_name', 'activity_name_counter', 200);
    setupCharCounter('purpose', 'purpose_counter', 1000);
    setupCharCounter('special_requirements', 'requirements_counter', 1000);

    // Trigger initial organization info display if value exists
    if (orgSelect.value) {
        orgSelect.dispatchEvent(new Event('change'));
    }

    // Form submission with loading overlay
    const form = document.getElementById('organizationBookingForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        // Basic validation
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (isValid) {
            loadingOverlay.classList.add('active');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        } else {
            e.preventDefault();
        }
    });

    // Real-time validation feedback
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required')) {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            }
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });
});
</script>
@endpush
@endsection
