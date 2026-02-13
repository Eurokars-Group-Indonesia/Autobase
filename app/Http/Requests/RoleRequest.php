<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->route('role');
        
        $rules = [
            'role_name' => 'required|string|max:50',
            'role_description' => 'required|string|max:200',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:ms_permissions,permission_id',
            'menus' => 'nullable|array',
            'menus.*' => 'exists:ms_menus,menu_id',
        ];

        // role_code validation
        if ($role) {
            $rules['role_code'] = [
                'required',
                'string',
                'max:10',
                Rule::unique('ms_role', 'role_code')
                    ->ignore($role->role_id, 'role_id')
                    ->where(function ($query) {
                        return $query->where('is_active', '1');
                    })
            ];
        } else {
            $rules['role_code'] = [
                'required',
                'string',
                'max:10',
                Rule::unique('ms_role', 'role_code')
                    ->where(function ($query) {
                        return $query->where('is_active', '1');
                    })
            ];
        }
        
        return $rules;
    }
}
