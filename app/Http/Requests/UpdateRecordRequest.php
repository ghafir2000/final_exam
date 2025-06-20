<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecordRequest extends FormRequest
{

    public function beforeAuthorize()
    {
        $user = Auth()->user();
        if ($user->userable_id != $this->booking->service->servicable_id) {
            
        }
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth()->user();
        if ($user->userable_id != $this->booking->service->servicable_id ||
            $user->userable_id != $this->booking->pet->customer_id) {
            abort(403, 'Error: You are not the owner of the record');
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
        return [
            'stats' => ['required', 'array'],
        ];
    }
}
