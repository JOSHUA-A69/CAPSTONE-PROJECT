<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationRequest extends FormRequest
{
    public function authorize()
    {
        // authorization handled by route middleware (staff role)
        return $this->user() != null;
    }

    public function rules()
    {
        $allowed = [
            'Himig Diwa Chorale',
            'Acolytes and Lectors',
            'Children of Mary',
            'Student Catholic Action',
            'Young Missionaries Club',
            'Catechetical Organization',
        ];

        return [
            'adviser_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'org_name' => ['required', 'string', 'max:255', Rule::in($allowed)],
            'org_desc' => ['nullable', 'string'],
        ];
    }
}
