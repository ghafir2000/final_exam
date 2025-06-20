<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userableTypes = [
            'App\Models\Customer',
            'App\Models\Veterinarian',
            'App\Models\Partner',
        ];

        // dd($userableTypes);

        $rules = [
            'name' => 'nullable|string',
            'email' => 'required|string|email|unique:users',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string',
            'country' => 'nullable|string',
            'userable_type' => [
                'required',
                'string',
                Rule::in($userableTypes),
            ],
        ];

        if ($this->input('userable_type') === 'App\Models\Veterinarian') {
            $rules += [
                'degree' => 'required|string',
                'degree_year' => 'required|integer|max:2100',
                'university' => 'required|string',
            ];
        }
        if ($this->input('userable_type') === 'App\Models\Customer') {
            $rules['customer_code'] = [
                'required',
                'string',
                'regex:/^[A-Za-z0-9]{8}$/',
                Rule::unique('customers', 'customer_code')
            ];
        }

        if ($this->input('userable_type') === 'App\Models\Partner') {
            $rules += [
                'website' => 'required|string',
            ];
        }

        return $rules;
    }

}
