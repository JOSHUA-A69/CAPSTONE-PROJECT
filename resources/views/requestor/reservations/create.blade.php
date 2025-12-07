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

    .dark .form-header {
        background: linear-gradient(135deg, #312e81 0%, #3730a3 100%);
        border-bottom: 3px solid #4f46e5;
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

    .dark .ministry-header {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        color: #f3f4f6;
    }

    .form-note {
        font-style: italic;
        font-size: 12px;
        padding: 10px !important;
        background: #fefce8;
        border-top: 2px solid #fde047;
        color: #854d0e;
    }

    .dark .form-note {
        background: #713f12;
        border-top: 2px solid #a16207;
        color: #fef3c7;
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

    /* Multi-select styles */
    .multi-select {
        padding: 8px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        cursor: pointer;
    }

    .dark .multi-select {
        background: #1f2937;
        border-color: #374151;
    }

    .multi-select option {
        padding: 8px 12px;
        margin: 2px 0;
        border-radius: 4px;
        cursor: pointer;
    }

    .multi-select option:hover {
        background-color: #f0f0f0;
    }

    .dark .multi-select option:hover {
        background-color: #374151;
    }

    .multi-select option:checked {
        background: linear-gradient(to right, #4F46E5, #7C3AED);
        color: white;
        font-weight: 600;
    }

    /* Checkbox list styles */
    .checkbox-list {
        display: flex;
        flex-direction: column;
        max-height: 200px;
        overflow-y: auto;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px;
        background: white;
    }

    .dark .checkbox-list {
        background: #1f2937;
        border-color: #374151;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        padding: 8px 10px;
        margin: 4px 0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        width: 100%;
    }

    .checkbox-item:hover {
        background: #f3f4f6;
    }

    .dark .checkbox-item:hover {
        background: #374151;
    }

    /* Hide default checkbox */
    .checkbox-item input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        width: 0;
        height: 0;
    }

    /* Custom checkbox */
    .checkbox-item .checkmark {
        display: inline-block;
        position: relative;
        height: 24px;
        width: 24px;
        background-color: #fff;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        margin-right: 12px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .dark .checkbox-item .checkmark {
        background-color: #374151;
        border-color: #6b7280;
    }

    /* Checkmark when checked */
    .checkbox-item input[type="checkbox"]:checked ~ .checkmark {
        background-color: #4F46E5;
        border-color: #4F46E5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .dark .checkbox-item input[type="checkbox"]:checked ~ .checkmark {
        background-color: #6366F1;
        border-color: #6366F1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    /* Checkmark icon */
    .checkbox-item .checkmark:after {
        content: "";
        position: absolute;
        display: none;
        left: 7px;
        top: 3px;
        width: 6px;
        height: 11px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .checkbox-item input[type="checkbox"]:checked ~ .checkmark:after {
        display: block;
    }

    .checkbox-item span:not(.checkmark) {
        font-size: 14px;
        color: #1f2937;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .dark .checkbox-item span:not(.checkmark) {
        color: #f3f4f6;
    }

    /* Highlight text when checked */
    .checkbox-item input[type="checkbox"]:checked ~ span:not(.checkmark) {
        color: #4F46E5;
        font-weight: 600;
    }

    .dark .checkbox-item input[type="checkbox"]:checked ~ span:not(.checkmark) {
        color: #818CF8;
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

        <style>
            .dark .no-print {
                background: #1e3a8a !important;
                border-bottom: 1px solid #3b82f6 !important;
            }
            .dark .no-print summary {
                color: #93c5fd !important;
            }
            .dark .no-print div {
                color: #dbeafe !important;
            }
        </style>

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
                            <span class="tooltiptext">Select all organizations that will participate in this activity. You can select multiple organizations.</span>
                        </span>
                    </label>
                    <div class="checkbox-list" style="max-height: 200px; overflow-y: auto; border: 2px solid #e0e0e0; border-radius: 8px; padding: 10px; background: white;">
                        @if($organizations->isEmpty())
                            <div style="color: #d97706; font-size: 12px; padding: 10px; text-align: center;">⚠️ No organizations available</div>
                        @else
                            @foreach($organizations as $o)
                                <label class="checkbox-item" style="display: flex; align-items: center; padding: 8px 10px; margin: 4px 0; border-radius: 6px; cursor: pointer; transition: background 0.2s;">
                                    <input 
                                        type="checkbox" 
                                        name="organization_ids[]" 
                                        value="{{ $o->org_id }}"
                                        @if(is_array(old('organization_ids')) && in_array($o->org_id, old('organization_ids'))) checked @endif
                                    >
                                    <span class="checkmark"></span>
                                    <span style="font-size: 14px;">{{ $o->org_name }}</span>
                                </label>
                            @endforeach
                        @endif
                    </div>
                    <p style="font-size: 12px; color: #666; margin-top: 5px; font-style: italic;">💡 Check all organizations that will participate in this activity</p>
                    @error('organization_ids')
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
                            <span class="tooltiptext">Select how you want to choose a priest for your spiritual activity</span>
                        </span>
                    </label>
                    
                    <!-- Priest Selection Type -->
                    <select
                        name="priest_selection_type"
                        id="priest_selection_type"
                        required
                        class="@error('priest_selection_type') is-invalid @enderror"
                        onchange="togglePriestOptions()"
                    >
                        <option value="">-- Select Option --</option>
                        <option value="specific" @if(old('priest_selection_type')=='specific') selected @endif>Select from SVD Priests</option>
                        <option value="any_available" @if(old('priest_selection_type')=='any_available') selected @endif>Any Available Priest (Admin will assign)</option>
                        <option value="external" @if(old('priest_selection_type')=='external') selected @endif>Already Have a Priest (External)</option>
                    </select>
                    <!-- Specific Priest Selection (shown when "Select from SVD Priests" is chosen) -->
                    <div id="specific_priest_div" style="display: none; margin-top: 10px;">
                        <label>
                            Choose Priest(s)<span class="required-indicator">*</span>
                            <span class="tooltip help-icon">
                                ?
                                <span class="tooltiptext">Select one or more priests for this reservation. All selected priests will be notified and can confirm their availability.</span>
                            </span>
                        </label>
                        <div class="checkbox-list">
                            @foreach($priests as $priest)
                                <label class="checkbox-item">
                                    <input 
                                        type="checkbox" 
                                        name="priest_ids[]" 
                                        value="{{ $priest->id }}"
                                        @if(is_array(old('priest_ids')) && in_array($priest->id, old('priest_ids'))) checked @endif
                                    >
                                    <span class="checkmark"></span>
                                    <span>{{ $priest->full_name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p style="font-size: 12px; color: #666; margin-top: 5px; font-style: italic;">💡 Check multiple priests if co-celebration is needed</p>
                        @error('priest_ids')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <!-- External Priest Details (shown when "Already Have a Priest" is chosen) -->
                    <div id="external_priest_div" style="display: none; margin-top: 10px;">
                        <div style="margin-bottom: 8px;">
                            <label>
                                Priest Name<span class="required-indicator">*</span>
                            </label>
                            <input
                                type="text"
                                name="external_priest_name"
                                id="external_priest_name"
                                value="{{ old('external_priest_name') }}"
                                placeholder="Enter priest's full name"
                                class="@error('external_priest_name') is-invalid @enderror"
                            />
                            @error('external_priest_name')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label>
                                Priest Contact (Optional)
                            </label>
                            <input
                                type="text"
                                name="external_priest_contact"
                                id="external_priest_contact"
                                value="{{ old('external_priest_contact') }}"
                                placeholder="Phone number or email"
                                class="@error('external_priest_contact') is-invalid @enderror"
                            />
                            @error('external_priest_contact')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Any Available Priest Info -->
                    <div id="any_available_info" style="display: none; margin-top: 10px; padding: 10px; background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 4px;">
                        <p style="margin: 0; font-size: 13px; color: #1e40af;">
                            <strong>ℹ️ Note:</strong> The admin will assign an available priest to your reservation and notify you once assigned.
                        </p>
                    </div>

                    <!-- External Priest Info -->
                    <div id="external_priest_info" style="display: none; margin-top: 10px; padding: 10px; background: #f0fdf4; border-left: 3px solid #16a34a; border-radius: 4px;">
                        <p style="margin: 0; font-size: 13px; color: #15803d;">
                            <strong>ℹ️ Note:</strong> Your reservation will be submitted for admin review. Please provide details of your external priest.
                        </p>
                    </div>
                </td>
            </tr>

            <tr>
                <!-- Service, Venue (50% width) -->
                <td style="width: 50%; vertical-align: top;">
                    <div style="margin-bottom: 8px;">
                        <label>
                            Service Category<span class="required-indicator">*</span>
                            <span class="tooltip help-icon">
                                ?
                                <span class="tooltiptext">Select the type of spiritual service you are requesting</span>
                            </span>
                        </label>
                        <select
                            name="service_category"
                            id="service_category"
                            required
                            onchange="toggleMassTypeField()"
                        >
                            <option value="">-- Select Service Category --</option>
                            <option value="institutional_mass" data-requires-mass-type="true">⛪ Institutional Mass</option>
                            <option value="non_institutional_mass" data-requires-mass-type="true">✝️ Non-Institutional Mass</option>
                            <option value="other_services" data-requires-mass-type="true">📌 Other Services</option>
                        </select>
                    </div>
                    
                    <!-- Mass Type Selection (Shows when Institutional or Non-Institutional Mass is selected) -->
                    <div id="mass_type_container" style="display: none; margin-bottom: 8px;">
                        <label>
                            Service Type<span class="required-indicator">*</span>
                            <span class="tooltip help-icon">
                                ?
                                <span class="tooltiptext">Select the specific service or type your custom service</span>
                            </span>
                        </label>
                        
                        <!-- Dropdown for predefined services -->
                        <div id="service_dropdown_container" style="display: none;">
                            <select
                                name="service_id"
                                id="service_id"
                                required
                                class="@error('service_id') is-invalid @enderror"
                            >
                                <option value="">-- Select Service Type --</option>
                                <optgroup label="Institutional Mass" id="institutional_mass_options" style="display: none;">
                                    @foreach($services->where('service_category', 'Institutional Mass') as $service)
                                        <option value="{{ $service->service_id }}">{{ $service->service_name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Non-Institutional Mass" id="non_institutional_mass_options" style="display: none;">
                                    @foreach($services->where('service_category', 'Non-Institutional Mass') as $service)
                                        <option value="{{ $service->service_id }}">{{ $service->service_name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('service_id')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Text input for Other Services -->
                        <div id="other_services_input_container" style="display: none;">
                            <input
                                type="text"
                                name="other_service_type"
                                id="other_service_type"
                                placeholder="Please enter the service type you want to request..."
                                maxlength="255"
                                class="@error('other_service_type') is-invalid @enderror"
                            >
                            @error('other_service_type')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>
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
            massTypeContainer.style.display = 'block';
            
            // Show appropriate mass type options
            if (serviceCategorySelect.value === 'institutional_mass') {
                serviceDropdownContainer.style.display = 'block';
                otherServicesInputContainer.style.display = 'none';
                serviceIdSelect.required = true;
                otherServiceTypeInput.required = false;
                institutionalOptions.style.display = 'block';
                nonInstitutionalOptions.style.display = 'none';
                serviceIdSelect.value = '';
                otherServiceTypeInput.value = '';
            } else if (serviceCategorySelect.value === 'non_institutional_mass') {
                serviceDropdownContainer.style.display = 'block';
                otherServicesInputContainer.style.display = 'none';
                serviceIdSelect.required = true;
                otherServiceTypeInput.required = false;
                institutionalOptions.style.display = 'none';
                nonInstitutionalOptions.style.display = 'block';
                serviceIdSelect.value = '';
                otherServiceTypeInput.value = '';
            } else if (serviceCategorySelect.value === 'other_services') {
                serviceDropdownContainer.style.display = 'none';
                otherServicesInputContainer.style.display = 'block';
                serviceIdSelect.required = false;
                otherServiceTypeInput.required = true;
                institutionalOptions.style.display = 'none';
                nonInstitutionalOptions.style.display = 'none';
                serviceIdSelect.value = '';
                otherServiceTypeInput.value = '';
            }
        } else {
            massTypeContainer.style.display = 'none';
            serviceDropdownContainer.style.display = 'none';
            otherServicesInputContainer.style.display = 'none';
            serviceIdSelect.required = false;
            otherServiceTypeInput.required = false;
            serviceIdSelect.value = '';
            otherServiceTypeInput.value = '';
            institutionalOptions.style.display = 'none';
            nonInstitutionalOptions.style.display = 'none';
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
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                isValid = false;
                
                // Get field label - clean version
                let label = getFieldLabel(field);
                if (label && !errorMessages.includes(label)) {
                    errorMessages.push(label);
                }
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
                errorMessages.push('Event date must be at least 7 days from today');
                isValid = false;
            }
        }

        // Validate phone number format
        const phoneInput = document.getElementById('contact_number');
        if (phoneInput && phoneInput.value) {
            const phonePattern = /^[0-9+\-\s()]+$/;
            if (!phonePattern.test(phoneInput.value)) {
                phoneInput.classList.add('is-invalid');
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
        
        // Check if we have a predefined label
        if (fieldLabels[field.id]) {
            return fieldLabels[field.id];
        }
        if (fieldLabels[field.name]) {
            return fieldLabels[field.name];
        }
        
        // Fallback: clean up field name
        let name = field.name || field.id || 'Field';
        return name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Show validation error modal
    function showValidationErrorModal(errors) {
        // Remove existing modal if any
        const existingModal = document.getElementById('validationErrorModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Create error list
        const uniqueErrors = [...new Set(errors)];
        const displayErrors = uniqueErrors.slice(0, 6);
        const errorListHtml = displayErrors.map(err => `
            <li style="display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid #f3f4f6;">
                <span style="color: #ef4444; font-size: 16px;">○</span>
                <span style="color: #374151;">${err}</span>
            </li>
        `).join('');
        
        const remainingCount = uniqueErrors.length - displayErrors.length;
        
        const modalHtml = `
            <div id="validationErrorModal" style="
                position: fixed;
                inset: 0;
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(0,0,0,0.4);
                backdrop-filter: blur(4px);
                animation: fadeIn 0.2s ease;
            " onclick="if(event.target === this) closeValidationErrorModal()">
                <div style="
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
                    max-width: 420px;
                    width: 90%;
                    overflow: hidden;
                    animation: slideUp 0.3s ease;
                ">
                    <!-- Header -->
                    <div style="
                        background: #fef2f2;
                        padding: 24px;
                        text-align: center;
                        border-bottom: 1px solid #fecaca;
                    ">
                        <div style="
                            width: 56px;
                            height: 56px;
                            background: #fee2e2;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin: 0 auto 12px;
                        ">
                            <svg width="28" height="28" fill="#dc2626" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 style="color: #991b1b; font-size: 20px; font-weight: 700; margin: 0;">
                            Almost there!
                        </h3>
                        <p style="color: #b91c1c; font-size: 14px; margin: 8px 0 0 0;">
                            Please complete the required fields
                        </p>
                    </div>
                    
                    <!-- Body -->
                    <div style="padding: 20px 24px;">
                        <p style="color: #6b7280; font-size: 13px; margin: 0 0 12px 0;">
                            The following fields need to be filled:
                        </p>
                        <ul style="
                            list-style: none;
                            padding: 0;
                            margin: 0;
                            max-height: 200px;
                            overflow-y: auto;
                        ">
                            ${errorListHtml}
                        </ul>
                        ${remainingCount > 0 ? `
                            <p style="color: #9ca3af; font-size: 12px; margin: 12px 0 0 0; text-align: center;">
                                + ${remainingCount} more field${remainingCount > 1 ? 's' : ''}
                            </p>
                        ` : ''}
                    </div>
                    
                    <!-- Footer -->
                    <div style="padding: 16px 24px 24px;">
                        <button onclick="closeValidationErrorModal()" style="
                            width: 100%;
                            background: #3b82f6;
                            color: white;
                            border: none;
                            padding: 14px 24px;
                            border-radius: 12px;
                            font-size: 15px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.2s;
                        " onmouseover="this.style.background='#2563eb'" 
                           onmouseout="this.style.background='#3b82f6'">
                            OK, I'll complete the form
                        </button>
                    </div>
                </div>
            </div>
            <style>
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideUp {
                    from { transform: translateY(20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
            </style>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Reset submit button state
        resetSubmitButton();
    }
    
    // Reset submit button to normal state
    function resetSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        if (submitBtn) submitBtn.disabled = false;
        if (submitText) submitText.style.display = 'inline';
        if (submitLoader) submitLoader.style.display = 'none';
        if (loadingOverlay) loadingOverlay.classList.remove('active');
    }

    // Close validation error modal
    function closeValidationErrorModal() {
        const modal = document.getElementById('validationErrorModal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 200);
        }
        
        // Scroll to first error field
        const form = document.getElementById('reservationForm');
        const firstError = form.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => firstError.focus(), 300);
        }
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
        
        // Toggle mass type field on load
        toggleMassTypeField();

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

        // Ensure button starts in normal state on page load
        submitBtn.disabled = false;
        submitText.style.display = 'inline';
        submitLoader.style.display = 'none';
        if (loadingOverlay) loadingOverlay.classList.remove('active');

        form.addEventListener('submit', function(e) {
            // Validate form first - don't show loading until validation passes
            if (!validateForm()) {
                e.preventDefault();
                // Ensure button is reset
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                submitLoader.style.display = 'none';
                if (loadingOverlay) loadingOverlay.classList.remove('active');
                return false;
            }

            // Check for availability conflicts using the global status variable
            if (typeof currentAvailabilityStatus !== 'undefined' && !currentAvailabilityStatus.available) {
                e.preventDefault();
                
                // Build a nice message
                let conflictMsg = 'Please resolve the scheduling conflicts before submitting:\n\n';
                if (currentAvailabilityStatus.messages && currentAvailabilityStatus.messages.length > 0) {
                    currentAvailabilityStatus.messages.forEach(msg => {
                        conflictMsg += '• ' + msg + '\n';
                    });
                } else {
                    conflictMsg += '• The selected time slot is not available';
                }
                
                alert(conflictMsg);
                
                // Ensure button is reset
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                submitLoader.style.display = 'none';
                if (loadingOverlay) loadingOverlay.classList.remove('active');
                return false;
            }

            // Validation passed - show loading state
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoader.style.display = 'inline';
            if (loadingOverlay) loadingOverlay.classList.add('active');
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

    // Toggle priest selection options
    function togglePriestOptions() {
        const selectionType = document.getElementById('priest_selection_type').value;
        const specificDiv = document.getElementById('specific_priest_div');
        const externalDiv = document.getElementById('external_priest_div');
        const anyAvailableInfo = document.getElementById('any_available_info');
        const externalInfo = document.getElementById('external_priest_info');
        const externalNameInput = document.getElementById('external_priest_name');

        console.log('Toggle priest options called, selection type:', selectionType);
        console.log('Specific div found:', specificDiv);

        // Hide all sections first
        if (specificDiv) specificDiv.style.display = 'none';
        if (externalDiv) externalDiv.style.display = 'none';
        if (anyAvailableInfo) anyAvailableInfo.style.display = 'none';
        if (externalInfo) externalInfo.style.display = 'none';

        // Remove required attributes
        if (externalNameInput) externalNameInput.removeAttribute('required');

        // Show appropriate section based on selection
        if (selectionType === 'specific') {
            if (specificDiv) {
                specificDiv.style.display = 'block';
                console.log('Showing specific priest div');
            }
        } else if (selectionType === 'any_available') {
            if (anyAvailableInfo) anyAvailableInfo.style.display = 'block';
            // No officiant needed - admin will assign
        } else if (selectionType === 'external') {
            if (externalDiv) externalDiv.style.display = 'block';
            if (externalInfo) externalInfo.style.display = 'block';
            if (externalNameInput) externalNameInput.setAttribute('required', 'required');
        }
    }

    // Call on page load to handle old values
    document.addEventListener('DOMContentLoaded', function() {
        togglePriestOptions();
        
        // Initialize availability checking
        initAvailabilityCheck();
    });

    // Availability checking functionality
    function initAvailabilityCheck() {
        const dateInput = document.getElementById('schedule_date');
        const timeInput = document.getElementById('schedule_time');
        const venueSelect = document.getElementById('venue_select');
        const priestCheckboxes = document.querySelectorAll('input[name="priest_ids[]"]');

        // Add event listeners for live availability checking
        if (dateInput) {
            dateInput.addEventListener('change', checkAvailability);
        }
        if (timeInput) {
            timeInput.addEventListener('change', checkAvailability);
        }
        if (venueSelect) {
            venueSelect.addEventListener('change', checkAvailability);
        }
        priestCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', checkAvailability);
        });
    }

    let availabilityTimeout = null;
    let currentAvailabilityStatus = { available: true }; // Track current availability
    
    async function checkAvailability() {
        // Debounce to avoid too many requests
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

        // Only check if we have date and time
        if (!date || !time) return;

        // Get selected priest (only if specific selection)
        let priestId = null;
        if (priestSelectionType === 'specific') {
            const selectedPriest = document.querySelector('input[name="priest_ids[]"]:checked');
            priestId = selectedPriest?.value;
        }

        // Skip if venue is custom
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
                
                // Also update priest and venue availability indicators
                await updatePriestAvailabilityUI();
                await updateVenueAvailabilityUI();
            }
        } catch (error) {
            console.error('Availability check failed:', error);
        }
    }

    function displayAvailabilityStatus(result) {
        // Store current availability status
        currentAvailabilityStatus = result;
        
        // Remove existing availability messages
        document.querySelectorAll('.availability-message').forEach(el => el.remove());

        if (result.available) {
            // Show success message
            showAvailabilityMessage('schedule_time', 'This time slot is available!', 'success');
        } else {
            // Show conflict messages
            if (!result.priest_available) {
                showAvailabilityMessage('specific_priest_div', 
                    result.messages[0] || 'Priest is not available at this time', 'error');
            }
            if (!result.venue_available) {
                const venueMsg = result.messages.find(m => m.includes('Venue')) || 
                    'Venue is not available at this time';
                showAvailabilityMessage('venue_select', venueMsg, 'error');
            }

            // Show suggestions
            if (result.suggestions && result.suggestions.length > 0) {
                result.suggestions.forEach(suggestion => {
                    if (suggestion.type === 'time') {
                        showAvailabilityMessage('schedule_time', suggestion.message, 'warning');
                    } else if (suggestion.type === 'priest') {
                        showAvailabilityMessage('specific_priest_div', suggestion.message, 'warning');
                    } else if (suggestion.type === 'venue') {
                        showAvailabilityMessage('venue_container', suggestion.message, 'warning');
                    }
                });
            }
        }
    }

    function showAvailabilityMessage(afterElementId, message, type) {
        const targetElement = document.getElementById(afterElementId);
        if (!targetElement) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = 'availability-message';
        
        const colors = {
            success: { bg: '#d1fae5', border: '#10b981', text: '#065f46', icon: '✓' },
            error: { bg: '#fee2e2', border: '#ef4444', text: '#991b1b', icon: '⚠️' },
            warning: { bg: '#fef3c7', border: '#f59e0b', text: '#92400e', icon: '💡' }
        };
        
        const style = colors[type] || colors.warning;
        
        messageDiv.style.cssText = `
            background: ${style.bg};
            border: 1px solid ${style.border};
            color: ${style.text};
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        `;
        
        messageDiv.innerHTML = `<span>${style.icon}</span> <span>${message}</span>`;
        
        // Insert after target element
        targetElement.parentNode.insertBefore(messageDiv, targetElement.nextSibling);
    }

    // Also mark unavailable options in dropdowns/checkboxes
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
                        const label = checkbox.closest('label') || checkbox.parentElement;
                        if (label) {
                            if (!priest.available) {
                                label.style.opacity = '0.5';
                                label.title = 'Not available at this time';
                                
                                // Add "busy" indicator
                                let busyBadge = label.querySelector('.busy-badge');
                                if (!busyBadge) {
                                    busyBadge = document.createElement('span');
                                    busyBadge.className = 'busy-badge';
                                    busyBadge.style.cssText = 'background: #ef4444; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 8px;';
                                    busyBadge.textContent = 'BUSY';
                                    label.appendChild(busyBadge);
                                }
                            } else {
                                label.style.opacity = '1';
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
                            option.textContent = option.textContent.replace(' (BUSY)', '') + ' (BUSY)';
                            option.style.color = '#ef4444';
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

@endsection
