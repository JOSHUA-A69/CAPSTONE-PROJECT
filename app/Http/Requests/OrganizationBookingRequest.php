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
            // Support both single organization_id (backward compat) and multiple organization_ids
            'organization_id' => 'required_without:organization_ids|nullable|exists:organizations,org_id',
            'organization_ids' => 'required_without:organization_id|nullable|array|min:1',
            'organization_ids.*' => 'exists:organizations,org_id',
            'organization_server_quantities' => 'required_with:organization_ids|array',
            'organization_server_quantities.*' => 'nullable|integer|min:1|max:30',
            'activity_name' => 'required|string|max:255|min:3',
            'purpose' => 'required|string|max:1000|min:10',
            'activity_details' => 'nullable|string|max:2000',
            'requested_date' => 'required|date|after:today',
            // Time in and time out fields for activity duration
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i|after:time_in',
            // Keep for backward compatibility but make optional
            'requested_time' => 'nullable|date_format:H:i',
            'requested_venue' => 'nullable|string|max:255',
            'estimated_participants' => 'nullable|integer|min:1|max:10000',
            'servers_needed' => 'nullable|integer|min:0|max:50',
            'special_requirements' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'organization_id.required_without' => 'Please select at least one organization for your request.',
            'organization_id.exists' => 'The selected organization is not valid.',
            'organization_ids.required_without' => 'Please select at least one organization for your request.',
            'organization_ids.min' => 'Please select at least one organization.',
            'organization_ids.*.exists' => 'One or more selected organizations are not valid.',
            'organization_server_quantities.required_with' => 'Please provide the server quantity for each selected organization.',
            'organization_server_quantities.*.integer' => 'Server quantity must be a whole number.',
            'organization_server_quantities.*.min' => 'Server quantity must be at least 1.',
            'organization_server_quantities.*.max' => 'Server quantity must not exceed 30 per organization.',
            'activity_name.required' => 'Activity name is required.',
            'activity_name.min' => 'Activity name must be at least 3 characters.',
            'activity_name.max' => 'Activity name cannot exceed 255 characters.',
            'purpose.required' => 'Please describe the purpose of your activity.',
            'purpose.min' => 'Purpose description must be at least 10 characters.',
            'purpose.max' => 'Purpose description cannot exceed 1000 characters.',
            'activity_details.max' => 'Activity details cannot exceed 2000 characters.',
            'requested_date.required' => 'Please specify your requested date.',
            'requested_date.date' => 'Please provide a valid date.',
            'requested_date.after' => 'Requested date must be in the future.',
            'time_in.required' => 'Please specify the start time of your activity.',
            'time_in.date_format' => 'Please provide a valid start time (HH:MM format).',
            'time_out.required' => 'Please specify the end time of your activity.',
            'time_out.date_format' => 'Please provide a valid end time (HH:MM format).',
            'time_out.after' => 'End time must be after the start time.',
            'requested_venue.max' => 'Venue name cannot exceed 255 characters.',
            'estimated_participants.integer' => 'Number of participants must be a whole number.',
            'estimated_participants.min' => 'Number of participants must be at least 1.',
            'estimated_participants.max' => 'Number of participants cannot exceed 10,000.',
            'servers_needed.integer' => 'Number of servers must be a whole number.',
            'servers_needed.min' => 'Number of servers cannot be negative.',
            'servers_needed.max' => 'Number of servers cannot exceed 50.',
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
            'organization_server_quantities' => 'organization server quantities',
            'activity_name' => 'activity name',
            'requested_date' => 'requested date',
            'estimated_participants' => 'estimated participants',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $organizationIds = collect($this->input('organization_ids', []))
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (string) $id)
                ->values();

            if ($organizationIds->isEmpty()) {
                return;
            }

            $quantities = (array) $this->input('organization_server_quantities', []);

            foreach ($organizationIds as $organizationId) {
                $quantity = $quantities[$organizationId] ?? null;

                if ($quantity === null || $quantity === '') {
                    $validator->errors()->add(
                        'organization_server_quantities.' . $organizationId,
                        'Please select a server quantity for each selected organization.'
                    );
                    continue;
                }

                if (!is_numeric($quantity) || (int) $quantity < 1 || (int) $quantity > 30) {
                    $validator->errors()->add(
                        'organization_server_quantities.' . $organizationId,
                        'Server quantity must be between 1 and 30 for each organization.'
                    );
                }
            }
        });
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
