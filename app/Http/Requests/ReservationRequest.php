<?php

namespace App\Http\Requests;

use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * @property string|null $venue_id
 * @property string|null $custom_venue
 * @property string|null $service_id
 * @property string|null $org_id
 * @property string|null $officiant_id
 * @property string|null $schedule_date
 * @property string|null $schedule_time
 * @property string|null $activity_name
 * @property string|null $theme
 * @property string|null $purpose
 * @property string|null $details
 * @property int|null $participants_count
 * @property string|null $commentator
 * @property string|null $servers
 * @property string|null $readers
 * @property string|null $choir
 * @property string|null $psalmist
 * @property string|null $prayer_leader
 */
class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() != null; // routes will enforce role-specific access
    }

    public function rules(): array
    {
        $rules = [
            // When selecting predefined services, require a valid service_id.
            // For "Other Services", we validate differently below.
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'service_id')],
            'service_category' => ['required', 'in:institutional_mass,non_institutional_mass,other_services'],
            'venue_id' => ['required'],
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['integer', Rule::exists('organizations', 'org_id')],
            'priest_selection_type' => ['required', 'in:specific,any_available,external'],
            'schedule_date' => ['required', 'date', 'after:now'],
            'schedule_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time' => ['required', 'after:schedule_date'],
            'activity_name' => ['required', 'string', 'max:255'],
            'theme' => ['nullable', 'string', 'max:1000'],
            'purpose' => ['nullable', 'string', 'max:150'],
            'details' => ['nullable', 'string'],
            'participants_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'commentator' => ['nullable', 'string', 'max:255'],
            'servers' => ['nullable', 'string', 'max:500'],
            'readers' => ['nullable', 'string', 'max:500'],
            'choir' => ['nullable', 'string', 'max:255'],
            'psalmist' => ['nullable', 'string', 'max:255'],
            'prayer_leader' => ['nullable', 'string', 'max:255'],
        ];

        // Priest selection validation based on type
        if ($this->priest_selection_type === 'specific') {
            $rules['priest_ids'] = ['required', 'array', 'min:1'];
            $rules['priest_ids.*'] = [
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', ['priest', 'admin']);
                }),
            ];
            // Validate main_celebrant_id if multiple priests selected
            if (is_array($this->priest_ids) && count($this->priest_ids) > 1) {
                $rules['main_celebrant_id'] = ['required', 'integer', 'in:' . implode(',', $this->priest_ids)];
            }
        } elseif ($this->priest_selection_type === 'external') {
            $rules['external_priest_name'] = ['required', 'string', 'max:255'];
            $rules['external_priest_contact'] = ['nullable', 'string', 'max:255'];
        }
        // For 'any_available', no priest_ids is required (admin will assign)

        // Service selection validation based on category
        if ($this->service_category === 'other_services') {
            // Require a custom service type string when "Other Services" is chosen
            $rules['other_service_type'] = ['required', 'string', 'max:50'];
            // service_id is not required in this path
            $rules['service_id'] = ['nullable', 'integer', Rule::exists('services', 'service_id')];
        } else {
            // Predefined services require a valid service_id
            $rules['service_id'] = ['required', 'integer', Rule::exists('services', 'service_id')];
        }

        // If custom venue is selected, require custom_venue field
        if ($this->venue_id === 'custom') {
            $rules['custom_venue'] = ['required', 'string', 'max:255'];
        } else {
            // Otherwise, venue_id must be a valid integer from venues table
            $rules['venue_id'] = ['required', 'integer', Rule::exists('venues', 'venue_id')];
        }

        return $rules;
    }

    /**
     * Configure the validator instance with double-booking prevention
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Skip double-booking check if there are already validation errors
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateDoubleBooking($validator);
        });
    }

    /**
     * Validate for double-booking conflicts
     */
    protected function validateDoubleBooking(Validator $validator): void
    {
        $availabilityService = app(AvailabilityService::class);

        // Get the schedule date and time
        $scheduleDateTime = $this->getScheduleDateTime();
        if (!$scheduleDateTime) {
            return;
        }

        // Get priest ID(s) if specific priest selected
        $priestId = null;
        if ($this->priest_selection_type === 'specific') {
            $priestIds = $this->input('priest_ids', []);
            $priestId = !empty($priestIds) ? (int) $priestIds[0] : null;
        }

        // Get venue ID (null if custom venue)
        $venueId = null;
        if ($this->venue_id !== 'custom' && is_numeric($this->venue_id)) {
            $venueId = (int) $this->venue_id;
        }

        // Get exclude reservation ID for edit scenarios
        $excludeReservationId = $this->route('reservation_id') ?? $this->route('reservation');

        // Check availability
        $result = $availabilityService->checkFullAvailability(
            $priestId,
            $venueId,
            $scheduleDateTime,
            $excludeReservationId
        );

        // Add validation errors with suggestions
        if (!$result['available']) {
            $messages = $result['messages'];
            $suggestions = $result['suggestions'];

            if (!$result['priest_available'] && $priestId) {
                $errorMsg = "The selected priest is not available at this time.";

                // Find time suggestions
                $timeSuggestion = collect($suggestions)->firstWhere('type', 'time');
                if ($timeSuggestion) {
                    $errorMsg .= " " . $timeSuggestion['message'];
                }

                // Find priest suggestions
                $priestSuggestion = collect($suggestions)->firstWhere('type', 'priest');
                if ($priestSuggestion) {
                    $errorMsg .= " Or select another priest.";
                }

                $validator->errors()->add('priest_ids', $errorMsg);
            }

            if (!$result['venue_available'] && $venueId) {
                $errorMsg = "The selected venue is not available at this time.";

                // Find venue suggestions
                $venueSuggestion = collect($suggestions)->firstWhere('type', 'venue');
                if ($venueSuggestion) {
                    $errorMsg .= " " . $venueSuggestion['message'];
                }

                $validator->errors()->add('venue_id', $errorMsg);
            }

            // Add general error if both conflicts exist
            if (!$result['priest_available'] && !$result['venue_available']) {
                $validator->errors()->add('schedule_date',
                    "This time slot is already taken. Please select another date, time, venue, or priest.");
            }
        }
    }

    /**
     * Get the combined schedule date time
     */
    protected function getScheduleDateTime(): ?Carbon
    {
        try {
            // Use the original input since prepareForValidation already merged them
            $dateInput = $this->input('schedule_date');
            $timeInput = $this->input('schedule_time');

            if ($dateInput && $timeInput) {
                // Check if date already has time component
                if (strpos($dateInput, ':') !== false) {
                    return Carbon::parse($dateInput);
                }
                return Carbon::parse($dateInput . ' ' . $timeInput);
            } elseif ($dateInput) {
                return Carbon::parse($dateInput);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'organization_ids.required' => 'Please select at least one organization.',
            'organization_ids.min' => 'Please select at least one organization.',
            'organization_ids.*.exists' => 'One or more selected organizations are invalid.',
            'priest_selection_type.required' => 'Please select how you would like to choose a priest.',
            'priest_selection_type.in' => 'Invalid priest selection option.',
            'priest_ids.required' => 'Please select at least one priest from the list.',
            'priest_ids.min' => 'Please select at least one priest.',
            'priest_ids.*.exists' => 'One or more selected priests are invalid.',
            'main_celebrant_id.required' => 'Please select a main celebrant when multiple priests are assigned.',
            'main_celebrant_id.in' => 'The selected main celebrant must be one of the assigned priests.',
            'external_priest_name.required' => 'Please provide the name of your external priest.',
            'service_category.required' => 'Please select a service category.',
            'service_category.in' => 'Invalid service category.',
            'other_service_type.required' => 'Please enter the service type you are requesting.',
            'end_time.required' => 'Please specify the time out for your reservation.',
            'end_time.after' => 'Time Out must be after Time In.',
        ];
    }

    /**
     * Prepare data for validation - combine date and time
     */
    protected function prepareForValidation()
    {
        $mergeData = [];

        if ($this->has('schedule_date') && $this->has('schedule_time')) {
            $mergeData['schedule_date'] = $this->schedule_date . ' ' . $this->schedule_time;
        }

        // Combine date with end_time to create a full datetime
        if ($this->has('schedule_date') && $this->has('end_time')) {
            $mergeData['end_time'] = $this->schedule_date . ' ' . $this->end_time;
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }
}
