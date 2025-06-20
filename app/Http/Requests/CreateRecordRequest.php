<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CreateRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth()->user();
        $booking = Booking::with('pet', 'service')->find($this->booking_id);
        // dd($user, $booking);
        if (($user->userable_id != $booking->service->servicable_id && $user->userable_type == $booking->service->servicable_type) &&
            ($user->userable_id != $booking->pet->customer_id) && $user->userable_type == 'App/Models/Customer') {
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
        // dd( $this->all());
        return [
            'stats' => 'nullable|array',
            'stats.*.key' => 'nullable|string|max:255', // Key can be optional if value is present
            'stats.*.value' => 'required_with:stats.*.key|nullable|string',
            'stats.notes' => 'nullable|string',
            'booking_id' => ['required', 'exists:bookings,id'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */

}
