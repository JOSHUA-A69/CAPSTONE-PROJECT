<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'service_id' => ['required', 'integer', Rule::exists('services', 'service_id')],
            'venue_id' => ['required'],
            'org_id' => ['required', 'integer', Rule::exists('organizations', 'org_id')],
            'priest_selection_type' => ['required', 'in:specific,any_available,external'],
            'schedule_date' => ['required', 'date', 'after:now'],
            'schedule_time' => ['nullable', 'date_format:H:i'],
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
            $rules['officiant_id'] = ['required', 'integer', Rule::exists('users', 'id')->whereIn('role', ['priest', 'admin'])];
        } elseif ($this->priest_selection_type === 'external') {
            $rules['external_priest_name'] = ['required', 'string', 'max:255'];
            $rules['external_priest_contact'] = ['nullable', 'string', 'max:255'];
        }
        // For 'any_available', no officiant_id is required (admin will assign)

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
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'org_id.required' => 'Please select your organization. This is required so the organization adviser can review your reservation.',
            'org_id.exists' => 'The selected organization is invalid.',
            'priest_selection_type.required' => 'Please select how you would like to choose a priest.',
            'priest_selection_type.in' => 'Invalid priest selection option.',
            'officiant_id.required' => 'Please select a priest from the list.',
            'external_priest_name.required' => 'Please provide the name of your external priest.',
        ];
    }

    /**
     * Prepare data for validation - combine date and time
     */
    protected function prepareForValidation()
    {
        if ($this->has('schedule_date') && $this->has('schedule_time')) {
            $this->merge([
                'schedule_date' => $this->schedule_date . ' ' . $this->schedule_time,
            ]);
        }
    }
}
