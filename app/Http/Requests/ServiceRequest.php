<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() != null; // role enforced by route middleware
    }

    public function rules(): array
    {
        $categories = [
            'Liturgical Celebrations',
            'Retreats and Recollections',
            'Prayer Services',
            'Outreach Activities',
            'Daily Noon Mass',
            'Catechetical Activities',
        ];

        return [
            'service_name' => ['required', 'string', 'max:50'],
            'service_category' => ['nullable', 'string', Rule::in($categories)],
            'description' => ['nullable', 'string'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:10080'], // minutes, up to 7 days
        ];
    }
}
