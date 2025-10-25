<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string|null $venue_id
 * @property string|null $custom_venue_name
 * @property string|null $service_id
 * @property string|null $org_id
 * @property string|null $officiant_id
 * @property string|null $preferred_officiant_id
 * @property string|null $schedule_date
 * @property string|null $activity_name
 * @property string|null $theme
 * @property string|null $purpose
 * @property int|null $participants_count
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
            'org_id' => ['nullable', 'integer', Rule::exists('organizations', 'org_id')],
            'officiant_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'priest')],
            'preferred_officiant_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'priest')],
            'schedule_date' => ['required', 'date', 'after:now'],
            'activity_name' => ['required', 'string', 'max:255'],
            'theme' => ['nullable', 'string', 'max:1000'],
            'purpose' => ['nullable', 'string', 'max:150'],
            'participants_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];

        // If custom venue is selected, require custom_venue field
        if ($this->venue_id === 'custom') {
            $rules['custom_venue_name'] = ['required', 'string', 'max:255'];
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
        // Support both separate date/time (legacy form) and datetime-local (new form)
        if ($this->has('schedule_date') && $this->has('schedule_time')) {
            $this->merge([
                'schedule_date' => $this->schedule_date . ' ' . $this->schedule_time,
            ]);
        } elseif ($this->has('schedule_date') && is_string($this->schedule_date)) {
            // Convert HTML5 datetime-local (YYYY-MM-DDTHH:MM) to space-separated format
            $this->merge([
                'schedule_date' => str_replace('T', ' ', $this->schedule_date),
            ]);
        }
    }
}
