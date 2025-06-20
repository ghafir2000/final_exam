<?php

namespace App\Http\Requests;

use App\Enums\BookingEnums;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
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
        // dd($this->all());
        return [
            'id' => ['required', 'exists:bookings,id'],
            'status' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!BookingEnums::isValid($value)) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }],
            'date' => ['nullable', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
        ];
    }
}

