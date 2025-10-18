<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'org_id' => ['nullable', 'integer', Rule::exists('organizations', 'org_id')],
            'officiant_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'priest')],
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
