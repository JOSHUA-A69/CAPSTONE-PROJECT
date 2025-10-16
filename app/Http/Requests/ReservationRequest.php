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
        return [
            'service_id' => ['required', 'integer', Rule::exists('services', 'service_id')],
            'venue_id' => ['required', 'integer', Rule::exists('venues', 'venue_id')],
            'org_id' => ['nullable', 'integer', Rule::exists('organizations', 'org_id')],
            'schedule_date' => ['required', 'date', 'after:now'],
            'purpose' => ['nullable', 'string', 'max:150'],
            'details' => ['nullable', 'string'],
            'participants_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
