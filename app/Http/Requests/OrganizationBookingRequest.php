<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['requestor', 'admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization_id' => 'required|exists:organizations,org_id',
            'activity_name' => 'required|string|max:255|min:3',
            'purpose' => 'required|string|max:1000|min:10',
            'activity_details' => 'nullable|string|max:2000',
            'requested_date' => 'required|date|after:today',
            'requested_venue' => 'nullable|string|max:255',
            'estimated_participants' => 'nullable|integer|min:1|max:10000',
            'special_requirements' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'organization_id.required' => 'Please select an organization for your request.',
            'organization_id.exists' => 'The selected organization is not valid.',
            'activity_name.required' => 'Activity name is required.',
            'activity_name.min' => 'Activity name must be at least 3 characters.',
            'activity_name.max' => 'Activity name cannot exceed 255 characters.',
            'purpose.required' => 'Please describe the purpose of your activity.',
            'purpose.min' => 'Purpose description must be at least 10 characters.',
            'purpose.max' => 'Purpose description cannot exceed 1000 characters.',
            'activity_details.max' => 'Activity details cannot exceed 2000 characters.',
            'requested_date.required' => 'Please specify your requested date and time.',
            'requested_date.date' => 'Please provide a valid date and time.',
            'requested_date.after' => 'Requested date must be in the future.',
            'requested_venue.max' => 'Venue name cannot exceed 255 characters.',
            'estimated_participants.integer' => 'Number of participants must be a whole number.',
            'estimated_participants.min' => 'Number of participants must be at least 1.',
            'estimated_participants.max' => 'Number of participants cannot exceed 10,000.',
            'special_requirements.max' => 'Special requirements cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'organization_id' => 'organization',
            'activity_name' => 'activity name',
            'requested_date' => 'requested date',
            'estimated_participants' => 'estimated participants',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation($validator)
    {
        if ($this->expectsJson()) {
            parent::failedValidation($validator);
        }

        // For web requests, add a flash message
        session()->flash('error', 'Please check the form for errors and try again.');
        parent::failedValidation($validator);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up the input data
        if ($this->has('activity_name')) {
            $this->merge([
                'activity_name' => trim($this->activity_name),
            ]);
        }

        if ($this->has('purpose')) {
            $this->merge([
                'purpose' => trim($this->purpose),
            ]);
        }

        if ($this->has('activity_details')) {
            $this->merge([
                'activity_details' => $this->activity_details ? trim($this->activity_details) : null,
            ]);
        }

        if ($this->has('requested_venue')) {
            $this->merge([
                'requested_venue' => $this->requested_venue ? trim($this->requested_venue) : null,
            ]);
        }

        if ($this->has('special_requirements')) {
            $this->merge([
                'special_requirements' => $this->special_requirements ? trim($this->special_requirements) : null,
            ]);
        }
    }
}