<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $menu = $this->route('menu');
        
        $rules = [
            'menu_name' => 'required|string|max:100',
            'menu_url' => 'nullable|string|max:255',
            'menu_icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:ms_menus,menu_id',
            'menu_order' => 'required|integer|min:0',
        ];

        // menu_code validation
        if ($menu) {
            $rules['menu_code'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('ms_menus', 'menu_code')
                    ->ignore($menu->menu_id, 'menu_id')
                    ->where(function ($query) {
                        return $query->where('is_active', '1');
                    })
            ];
        } else {
            $rules['menu_code'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('ms_menus', 'menu_code')
                    ->where(function ($query) {
                        return $query->where('is_active', '1');
                    })
            ];
        }
        
        return $rules;
    }
}
