<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class ImageRequest extends FormRequest
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
        // Combine all allowed model classes into a single array
        $allowedModels = [
            'App\Models\User',
            'App\Models\Animal',
            'App\Models\Breed',
            'App\Models\Blog',
            'App\Models\Comment',
            'App\Models\Brand',
            'App\Models\Message',
            'App\Models\Pet',
            'App\Models\Post',
            'App\Models\Product',
            'App\Models\Service',
        ];

        // In your validation logic:
        $modelsWithRequiredModelId = [
            'App\Models\Blog',
            'App\Models\Comment',
            'App\Models\Message',
            'App\Models\Pet',
            'App\Models\Post',
            'App\Models\Product',
            'App\Models\Service',
        ];

        return [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'collection' => 'nullable|string', // Optional, default to 'default'
            'model_id' => [
                'required_if:model,' . implode(',', $modelsWithRequiredModelId),
                'integer',
            ],
            'model' => [
                'required',
                'string',
                Rule::in($allowedModels), // Pass the single combined array to Rule::in()
            ],
        ];
    }
}