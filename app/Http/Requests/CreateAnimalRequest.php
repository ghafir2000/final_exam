<?php

namespace App\Http\Requests;

use App\Rules\BreedRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateAnimalRequest extends FormRequest
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
        // dd($this->number_of_breeds);
        return [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'number_of_breeds' => ['required', 'integer', 'min:1'],
            'breeds' => ['required', 'array', 'size:' . (int) $this->number_of_breeds],
            'breeds.*.name' => ['required', 'string'],
            'breeds.*.description' => ['required', 'string'],
        ];
    }
}
