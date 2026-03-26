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
        return [
            'adviser_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'org_name' => ['required', 'string', 'max:255'],
            'custom_org_name' => ['nullable', 'string', 'max:255'],
            'org_desc' => ['nullable', 'string'],
        ];
    }
}
