<?php

namespace App\Http\Requests;

use App\Enums\PaymentEnums;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
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
        return [
            'status' => ['required', function ($attribute, $value, $fail) {
                if (!PaymentEnums::isValid($value)) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }]
        ];
    }
}
