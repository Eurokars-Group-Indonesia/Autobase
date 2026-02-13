<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $brand = $this->route('brand');
        
        $rules = [
            'brand_name' => 'required|string|max:100',
            'brand_group' => 'nullable|string|max:100',
            'country_origin' => 'nullable|string|max:100',
        ];

        // brand_code validation
        if ($brand) {
            $rules['brand_code'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('ms_brand', 'brand_code')
                    ->ignore($brand->brand_id, 'brand_id')
                    ->where(function ($query) {
                        return $query->where('is_active', '1');
                    })
            ];
        } else {
            $rules['brand_code'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('ms_brand', 'brand_code')
                    ->where(function ($query) {
                        return $query->where('is_active', '1');
                    })
            ];
        }
        
        return $rules;
    }
}
