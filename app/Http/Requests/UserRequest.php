<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // SSO fields are read-only, no validation needed for name, full_name, email, phone
            'dealer_id' => 'nullable|exists:ms_dealers,dealer_id',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:ms_role,role_id',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:ms_brand,brand_id',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'dealer_id.exists' => 'Selected dealer is invalid.',
            'roles.*.exists' => 'Selected role is invalid.',
            'brands.*.exists' => 'Selected brand is invalid.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator);
    }
}
