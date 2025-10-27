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
        border-radius: 2px;
        overflow: hidden;
    }

    /* Validation States */
    .form-table input.is-invalid,
    .form-table select.is-invalid,
    .form-table textarea.is-invalid {
        background: #fef2f2;
        border-bottom: 2px solid #dc2626 !important;
    }

    .form-table input.is-valid,
    .form-table select.is-valid,
    .form-table textarea.is-valid {
        background: #f0fdf4;
        border-bottom: 2px solid #16a34a !important;
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
        background: #2563eb;
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
        border-top: 4px solid #2563eb;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }    .form-header {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 18px;
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: #ffffff;
        text-transform: uppercase;
        border-bottom: 3px solid #1e40af;
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

    .form-table label {
        font-weight: 600;
        display: inline;
        margin-right: 6px;
        color: #374151;
        font-size: 13px;
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

    .form-table input[type="text"]:focus,
    .form-table input[type="date"]:focus,
    .form-table input[type="time"]:focus,
    .form-table input[type="number"]:focus,
    .form-table select:focus,
    .form-table textarea:focus {
        background: #f9fafb;
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

    .ministry-header {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        font-weight: 700;
        text-align: left;
        color: #1f2937;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px !important;
    }

    .form-note {
        font-style: italic;
        font-size: 12px;
        padding: 10px !important;
        background: #fefce8;
        border-top: 2px solid #fde047;
        color: #854d0e;
    }

    .form-actions {
        padding: 20px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-actions .office-label {
        font-size: 11px;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.4);
        transform: translateY(-1px);
    }

    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    /* ========================================
       PHASE B: MOBILE RESPONSIVENESS
       ======================================== */

    /* Mobile: 320px - 767px */
    @media (max-width: 767px) {
        .form-container {
            margin: 0.5rem;
            border-radius: 0;
            box-shadow: none;
        }

        .form-header {
            font-size: 14px;
            padding: 12px;
        }

        /* Stack table cells vertically */
        .form-table td {
            display: block;
            width: 100% !important;
            border-left: none !important;
            border-right: none !important;
            padding: 12px 14px;
        }

        .form-table tr {
            display: block;
            margin-bottom: 0;
        }

        .form-table td:first-child {
            border-top: 1px solid #d1d5db;
        }

        /* Larger fonts for mobile readability */
        .form-table {
            font-size: 14px;
        }

        .form-table label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .form-table input[type="text"],
        .form-table input[type="date"],
        .form-table input[type="time"],
        .form-table input[type="number"],
        .form-table select,
        .form-table textarea {
            font-size: 16px; /* Prevents iOS zoom on focus */
            padding: 10px 12px;
            min-height: 44px; /* Touch-friendly */
        }

        .form-table textarea {
            min-height: 100px;
        }

        /* Adjust help icons for mobile */
        .help-icon {
            width: 20px;
            height: 20px;
            font-size: 12px;
        }

        .tooltip .tooltiptext {
            width: calc(100vw - 40px);
            left: 50%;
            transform: translateX(-50%);
            margin-left: 0;
            bottom: auto;
            top: 125%;
        }

        .tooltip .tooltiptext::after {
            top: auto;
            bottom: 100%;
            border-color: transparent transparent #1f2937 transparent;
        }

        /* Mobile-friendly character counters */
        .char-counter {
            font-size: 11px;
            margin-top: 4px;
        }

        /* Stack buttons vertically */
        .form-actions {
            flex-direction: column;
            gap: 12px;
            padding: 16px;
        }

        .office-label {
            text-align: center;
            margin-bottom: 8px;
        }

        .btn-group {
            width: 100%;
            flex-direction: column-reverse; /* Submit on top */
        }

        .btn {
            width: 100%;
            min-height: 48px; /* Touch-friendly */
            font-size: 15px;
        }

        /* Ministry volunteers section */
        .ministry-header {
            font-size: 12px;
            padding: 12px !important;
        }

        /* Error messages more visible on mobile */
        .error-message {
            font-size: 12px;
            margin-top: 4px;
        }
    }

    /* Tablet: 768px - 1023px */
    @media (min-width: 768px) and (max-width: 1023px) {
        .form-container {
            margin: 1.5rem;
            max-width: 100%;
        }

        .form-table {
            font-size: 13px;
        }

        .form-table input[type="text"],
        .form-table input[type="date"],
        .form-table input[type="time"],
        .form-table input[type="number"],
        .form-table select,
        .form-table textarea {
            font-size: 14px;
            padding: 8px 10px;
        }

        .btn {
            min-height: 44px;
        }
    }

    /* Desktop: 1024px+ */
    @media (min-width: 1024px) {
        .form-container {
            max-width: 900px;
        }
    }

    /* Landscape mobile adjustments */
    @media (max-width: 767px) and (orientation: landscape) {
        .form-header {
            padding: 10px;
            font-size: 13px;
        }

        .form-table td {
            padding: 10px 12px;
        }
    }

    /* High DPI screens (Retina) */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .form-table {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    }
</style>

<div class="form-container">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div style="text-align: center; color: white;">
            <div class="spinner"></div>
            <p style="margin-top: 16px; font-size: 14px;">Submitting your request...</p>
        </div>
    </div>

    <!-- Validation Errors Summary -->
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

    <form method="POST" action="{{ route('requestor.reservations.store') }}" id="reservationForm" novalidate>
        @csrf

        <!-- Form Header -->
        <div class="form-header">
            🕊️ Spiritual Activity Request Form
        </div>

        <!-- Help Section -->
        <div class="no-print" style="background: #eff6ff; border-bottom: 1px solid #bfdbfe; padding: 12px 16px;">
            <details style="cursor: pointer;">
                <summary style="font-weight: 600; font-size: 12px; color: #1e40af; user-select: none;">
                    📖 Need help filling this form? Click here
                </summary>
                <div style="margin-top: 8px; font-size: 11px; color: #1e3a8a; line-height: 1.6;">
                    <p><strong>Required fields are marked with <span style="color: #dc2626;">*</span></strong></p>
                    <ul style="margin: 8px 0 0 20px; list-style: disc;">
                        <li>Provide complete and accurate information</li>
                        <li>Requests must be submitted at least 7 days before the event</li>
                        <li>Write "N/A" in fields that don't apply to your request</li>
                        <li>Contact information will be used for updates and confirmations</li>
                    </ul>
                </div>
            </details>
        </div>

        <!-- Form Table -->
        <table class="form-table">
            <tr>
                <!-- Name of Activity (60% width) -->
                <td style="width: 60%;">
                    <label for="activity_name">
                        Name of Activity<span class="required-indicator" aria-label="required">*</span>
                        <span class="tooltip help-icon" role="tooltip">
                            ?
                            <span class="tooltiptext">Enter the complete official name of your spiritual activity or event (e.g., "Send-Off Mass for BSET Board Takers")</span>
                        </span>
                    </label>
                    <input
                        type="text"
                        name="activity_name"
                        id="activity_name"
                        value="{{ old('activity_name') }}"
                        required
                        aria-required="true"
                        aria-describedby="activity_name_counter activity_name_help"
                        maxlength="200"
                        placeholder="e.g., Send-Off Mass for BSET Board Takers"
                        class="@error('activity_name') is-invalid @enderror"
                        @error('activity_name') aria-invalid="true" aria-describedby="activity_name_error" @enderror
                    >
                    <div class="char-counter" id="activity_name_counter" aria-live="polite">0 / 200 characters</div>
                    <span id="activity_name_help" class="sr-only">Enter the complete official name of your spiritual activity or event</span>
                    @error('activity_name')
                        <div class="error-message" id="activity_name_error" role="alert">⚠️ {{ $message }}</div>
                    @enderror
                </td>
                <!-- Date & Time (40% width) -->
                <td style="width: 40%;">
                    <div style="margin-bottom: 8px;">
                        <label for="schedule_date">
                            Date of Activity<span class="required-indicator" aria-label="required">*</span>
                            <span class="tooltip help-icon" role="tooltip">
                                ?
                                <span class="tooltiptext">Select the date of your event. Must be at least 7 days from today.</span>
                            </span>
                        </label>
                        <input
                            type="date"
                            name="schedule_date"
                            id="schedule_date"
                            value="{{ old('schedule_date') }}"
                            required
                            aria-required="true"
                            aria-describedby="schedule_date_help"
                            min="{{ date('Y-m-d', strtotime('+7 days')) }}"
                            class="@error('schedule_date') is-invalid @enderror"
                            @error('schedule_date') aria-invalid="true" aria-describedby="schedule_date_error" @enderror
                        >
                        <span id="schedule_date_help" class="sr-only">Select the date of your event. Must be at least 7 days from today.</span>
                        @error('schedule_date')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label>
                            Time<span class="required-indicator">*</span>
                        </label>
                        <input
                            type="time"
                            name="schedule_time"
                            id="schedule_time"
                            value="{{ old('schedule_time', '08:00') }}"
                            required
                            class="@error('schedule_time') is-invalid @enderror"
                        >
                        @error('schedule_time')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>
                </td>
            </tr>

            <tr>
                <!-- Theme (60% width) -->
                <td style="width: 60%;">
                    <label>
                        Theme
                        <span class="tooltip help-icon">
                            ?
                            <span class="tooltiptext">Provide the theme or message of your spiritual activity (e.g., "Empowered by Faith, Guided to Serve")</span>
                        </span>
                    </label>
                    <textarea
                        name="theme"
                        id="theme"
                        rows="2"
                        maxlength="500"
                        placeholder="e.g., Empowered by Faith, Guided to Serve"
                        class="@error('theme') is-invalid @enderror"
                    >{{ old('theme') }}</textarea>
                    <div class="char-counter" id="theme_counter">0 / 500 characters</div>
                    @error('theme')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
                <!-- Expected Number of Participants (40% width) -->
                <td style="width: 40%;">
                    <label>
                        Expected Number of Participants
                        <span class="tooltip help-icon">
                            ?
                            <span class="tooltiptext">Estimate the number of people expected to attend your event</span>
                        </span>
                    </label>
                    <input
                        type="number"
                        name="participants_count"
                        id="participants_count"
                        value="{{ old('participants_count') }}"
                        min="1"
                        max="10000"
                        placeholder="e.g., 35"
                        class="@error('participants_count') is-invalid @enderror"
                    >
                    @error('participants_count')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <tr>
                <!-- Requesting Office/Group (full width) -->
                <td colspan="2">
                    <label>
                        Requesting Office/Group <span style="color: red;">*</span>
                        <span class="tooltip help-icon">
                            ?
                            <span class="tooltiptext">Select your organization - required for reservation approval</span>
                        </span>
                    </label>
                    <select
                        name="org_id"
                        id="org_id"
                        required
                        @if($organizations->isEmpty()) disabled @endif
                        class="@error('org_id') is-invalid @enderror"
                    >
                        <option value="">-- Select Organization --</option>
                        @foreach($organizations as $o)
                            <option value="{{ $o->org_id }}" @if(old('org_id')==$o->org_id) selected @endif>{{ $o->org_name }}</option>
                        @endforeach
                    </select>
                    @if($organizations->isEmpty())
                        <div style="color: #d97706; font-size: 10px; margin-top: 4px;">⚠️ No organizations available</div>
                    @endif
                    @error('org_id')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <tr>
                <!-- Contact Person (50% width) -->
                <td style="width: 50%;">
                    <label>
                        Contact Person<span class="required-indicator">*</span>
                    </label>
                    <input
                        type="text"
                        name="contact_person"
                        id="contact_person"
                        value="{{ old('contact_person', auth()->user()->full_name) }}"
                        required
                        maxlength="100"
                        class="@error('contact_person') is-invalid @enderror"
                    >
                    @error('contact_person')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
                <!-- Contact Number (50% width) -->
                <td style="width: 50%;">
                    <label>
                        Contact Number<span class="required-indicator">*</span>
                        <span class="tooltip help-icon">
                            ?
                            <span class="tooltiptext">Provide a valid mobile number (e.g., 09XX XXX XXXX) where we can reach you</span>
                        </span>
                    </label>
                    <input
                        type="text"
                        name="contact_number"
                        id="contact_number"
                        value="{{ old('contact_number', auth()->user()->phone) }}"
                        required
                        maxlength="15"
                        placeholder="09XX XXX XXXX"
                        pattern="[0-9+\-\s()]+"
                        class="@error('contact_number') is-invalid @enderror"
                    >
                    @error('contact_number')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <tr>
                <!-- Officiant/Priest (full width) -->
                <td colspan="2">
                    <label>
                        Officiant/Priest<span class="required-indicator">*</span>
                        <span class="tooltip help-icon">
                            ?
                            <span class="tooltiptext">Select the priest or presider you prefer for your spiritual activity</span>
                        </span>
                    </label>
                    <select
                        name="officiant_id"
                        id="officiant_id"
                        required
                        class="@error('officiant_id') is-invalid @enderror"
                    >
                        <option value="">-- Select Priest/Presider --</option>
                        @foreach($priests as $priest)
                            <option value="{{ $priest->id }}" @if(old('officiant_id')==$priest->id) selected @endif>{{ $priest->full_name }}</option>
                        @endforeach
                    </select>
                    @error('officiant_id')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <tr>
                <!-- Service, Venue (50% width) -->
                <td style="width: 50%; vertical-align: top;">
                    <div style="margin-bottom: 8px;">
                        <label>
                            Service Type<span class="required-indicator">*</span>
                            <span class="tooltip help-icon">
                                ?
                                <span class="tooltiptext">Select the type of spiritual service you are requesting</span>
                            </span>
                        </label>
                        <select
                            name="service_id"
                            id="service_id"
                            required
                            class="@error('service_id') is-invalid @enderror"
                        >
                            <option value="">-- Select Service --</option>
                            @foreach($services as $s)
                                <option value="{{ $s->service_id }}" @if(old('service_id')==$s->service_id) selected @endif>{{ $s->service_name }}</option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label>
                            Venue<span class="required-indicator">*</span>
                            <span class="tooltip help-icon">
                                ?
                                <span class="tooltiptext">Choose the venue for your event. Select "Other/Custom" to specify a different location</span>
                            </span>
                        </label>
                        <select
                            name="venue_id"
                            id="venue_select"
                            required
                            onchange="toggleCustomVenue()"
                            class="@error('venue_id') is-invalid @enderror"
                        >
                            <option value="">-- Select Venue --</option>
                            @foreach($venues as $v)
                                <option value="{{ $v->venue_id }}" @if(old('venue_id')==$v->venue_id) selected @endif>{{ $v->name }}</option>
                            @endforeach
                            <option value="custom" @if(old('venue_id')=='custom') selected @endif>Other/Custom</option>
                        </select>
                        <div id="custom_venue_container" style="display: none; margin-top: 4px;">
                            <input
                                type="text"
                                name="custom_venue"
                                id="custom_venue_input"
                                placeholder="Specify exact location"
                                maxlength="200"
                                class="@error('custom_venue') is-invalid @enderror"
                            >
                        </div>
                        @error('venue_id')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                        @error('custom_venue')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>
                </td>
                <!-- Reason for the Celebration (50% width) -->
                <td style="width: 50%;">
                    <label>
                        Reason for the Celebration
                        <span class="tooltip help-icon">
                            ?
                            <span class="tooltiptext">Briefly describe the purpose or reason for this spiritual activity</span>
                        </span>
                    </label>
                    <textarea
                        name="purpose"
                        id="purpose"
                        rows="4"
                        maxlength="1000"
                        placeholder="Brief purpose or reason for this celebration"
                        class="@error('purpose') is-invalid @enderror"
                    >{{ old('purpose') }}</textarea>
                    <div class="char-counter" id="purpose_counter">0 / 1000 characters</div>
                    @error('purpose')
                        <div class="error-message">⚠️ {{ $message }}</div>
                    @enderror
                </td>
            </tr>

            <!-- Ministry Volunteers Header -->
            <tr>
                <td colspan="2" class="ministry-header">
                    ✝ Ministry Volunteers (Please indicate names or write N/A if not applicable)
                </td>
            </tr>

            <tr>
                <!-- Commentator -->
                <td>
                    <label>Commentator:</label>
                    <input
                        type="text"
                        name="commentator"
                        value="{{ old('commentator') }}"
                        placeholder="Write N/A if not applicable"
                        maxlength="100"
                    >
                </td>
                <!-- Servers -->
                <td>
                    <label>Servers:</label>
                    <input
                        type="text"
                        name="servers"
                        value="{{ old('servers') }}"
                        placeholder="Write N/A if not applicable"
                        maxlength="100"
                    >
                </td>
            </tr>

            <tr>
                <!-- Choir -->
                <td>
                    <label>Choir:</label>
                    <input
                        type="text"
                        name="choir"
                        value="{{ old('choir') }}"
                        placeholder="Write N/A if not applicable"
                        maxlength="100"
                    >
                </td>
                <!-- Readers -->
                <td>
                    <label>Readers:</label>
                    <input
                        type="text"
                        name="readers"
                        value="{{ old('readers') }}"
                        placeholder="Write N/A if not applicable"
                        maxlength="100"
                    >
                </td>
            </tr>

            <tr>
                <!-- Psalmist -->
                <td>
                    <label>Psalmist:</label>
                    <input
                        type="text"
                        name="psalmist"
                        value="{{ old('psalmist') }}"
                        placeholder="Write N/A if not applicable"
                        maxlength="100"
                    >
                </td>
                <!-- Leader for Prayer of the Faithful -->
                <td>
                    <label>Leader for Prayer of the Faithful:</label>
                    <input
                        type="text"
                        name="prayer_leader"
                        value="{{ old('prayer_leader') }}"
                        placeholder="Write N/A if not applicable"
                        maxlength="100"
                    >
                </td>
            </tr>

            <tr>
                <!-- Remarks/Other Requests (full width) -->
                <td colspan="2">
                    <label>
                        Remarks/Other Requests
                        <span class="tooltip help-icon">
                            ?
                            <span class="tooltiptext">Include any additional information, special requests, or important notes for your event</span>
                        </span>
                    </label>
                    <textarea
                        name="details"
                        id="details"
                        rows="2"
                        maxlength="1000"
                        placeholder="Write N/A if you have no additional requests"
                    >{{ old('details') }}</textarea>
                    <div class="char-counter" id="details_counter">0 / 1000 characters</div>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="form-note">
                    <strong>📌 Note:</strong> Write <strong>N/A</strong> in fields that are not applicable.
                </td>
            </tr>
        </table>

        <!-- Submit Buttons -->
        <div class="form-actions no-print">
            <div class="office-label">
                Holy Name University - CREaM Office
            </div>
            <div class="btn-group">
                <a href="{{ route('requestor.reservations.index') }}" class="btn btn-cancel">
                    Cancel
                </a>
                <button type="submit" id="submitBtn" class="btn btn-submit">
                    <span id="submitText">Submit Request</span>
                    <span id="submitLoader" style="display: none;">
                        <svg style="display: inline-block; width: 16px; height: 16px; margin-right: 8px; animation: spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                            <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                    </span>
                </button>
            </div>
        </div>
    </form>
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
            counter.classList.remove('warning', 'danger');
            if (length > maxLength * 0.9) {
                counter.classList.add('danger');
            } else if (length > maxLength * 0.75) {
                counter.classList.add('warning');
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
            customContainer.style.display = 'block';
            customInput.required = true;
        } else {
            customContainer.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    }

    // Form validation
    function validateForm() {
        const form = document.getElementById('reservationForm');
        let isValid = true;

        // Check required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        });

        // Validate date (must be at least 7 days from now)
        const dateInput = document.getElementById('schedule_date');
        if (dateInput && dateInput.value) {
            const selectedDate = new Date(dateInput.value);
            const minDate = new Date();
            minDate.setDate(minDate.getDate() + 7);

            if (selectedDate < minDate) {
                dateInput.classList.add('is-invalid');
                alert('Event date must be at least 7 days from today.');
                isValid = false;
            }
        }

        // Validate phone number format
        const phoneInput = document.getElementById('contact_number');
        if (phoneInput && phoneInput.value) {
            const phonePattern = /^[0-9+\-\s()]+$/;
            if (!phonePattern.test(phoneInput.value)) {
                phoneInput.classList.add('is-invalid');
                alert('Please enter a valid phone number.');
                isValid = false;
            }
        }

        return isValid;
    }

    // Real-time validation on blur
    function addFieldValidation(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.addEventListener('blur', function() {
            if (field.hasAttribute('required')) {
                if (field.value.trim()) {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                } else {
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                }
            }
        });

        // Remove validation classes on focus
        field.addEventListener('focus', function() {
            field.classList.remove('is-invalid', 'is-valid');
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle custom venue on load
        toggleCustomVenue();

        // Initialize character counters
        updateCharCounter('activity_name', 'activity_name_counter', 200);
        updateCharCounter('theme', 'theme_counter', 500);
        updateCharCounter('purpose', 'purpose_counter', 1000);
        updateCharCounter('details', 'details_counter', 1000);

        // Add validation to key fields
        const validationFields = [
            'activity_name', 'schedule_date', 'schedule_time',
            'contact_person', 'contact_number', 'officiant_id',
            'service_id', 'venue_select'
        ];

        validationFields.forEach(fieldId => addFieldValidation(fieldId));

        // Form submit handler
        const form = document.getElementById('reservationForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');
        const loadingOverlay = document.getElementById('loadingOverlay');

        form.addEventListener('submit', function(e) {
            // Validate form
            if (!validateForm()) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                return false;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoader.style.display = 'inline';
            loadingOverlay.classList.add('active');
        });

        // Auto-save to localStorage (optional - uncomment to enable)
        /*
        const autoSaveInterval = setInterval(() => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            localStorage.setItem('reservationDraft', JSON.stringify(data));
        }, 30000); // Save every 30 seconds

        // Load draft on page load
        const draft = localStorage.getItem('reservationDraft');
        if (draft && confirm('Found a saved draft. Would you like to load it?')) {
            const data = JSON.parse(draft);
            Object.keys(data).forEach(key => {
                const field = form.elements[key];
                if (field) field.value = data[key];
            });
        }
        */
    });
</script>

@endsection
