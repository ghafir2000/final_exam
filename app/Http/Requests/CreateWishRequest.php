<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\Service;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateWishRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        if ($user->userable_type !== 'App\Models\Customer' &&
            in_array($this->wishable_type, [Product::class, Service::class])) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $allowedModels = [
            'App\Models\Blog',
            'App\Models\Comment',
            'App\Models\Post',
            'App\Models\Product',
            'App\Models\Service',
        ];
        return [
            'wishable_id' => ['required'],
            'wishable_type' => ['required', Rule::in($allowedModels)],
        ];
    }
}
