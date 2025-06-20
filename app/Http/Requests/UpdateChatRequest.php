<?php

namespace App\Http\Requests;

use App\Enums\ChatEnums;
use Illuminate\Foundation\Http\FormRequest;

class UpdateChatRequest extends FormRequest
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
            'id' => 'required|exists:chats,id',
            'status' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (!ChatEnums::isValid($value)) {
                    $fail('The ' . $attribute . ' is invalid.');
                }
            }],
        ];
    }
}
