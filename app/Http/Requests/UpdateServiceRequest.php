<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator; // Import Validator
use App\Models\Booking; // Assuming your Booking model is here
use Carbon\Carbon;
use Carbon\CarbonInterval;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust as per your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the service being updated. Assumes route model binding.
        // If your route parameter is named differently, adjust 'service'.
        $service = $this->route('service');
        $serviceId = $service ? $service->id : null;

        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0', // Price should be numeric
            'description' => 'required|string',
            'duration' => [
                'required',
                'string',
            ],
            'available_times' => [
                'required',
                'json', // Ensures the input is a valid JSON string
                function ($attribute, $value, $fail) use ($serviceId) {
                    $availableTimes = json_decode($value, true);

                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($availableTimes)) {
                        // This should ideally be caught by the 'json' rule, but double-check
                        $fail('The ' . $attribute . ' must be a valid JSON array of times.');
                        return;
                    }

                    if (empty($availableTimes)) {
                        $fail('The ' . $attribute . ' field cannot be empty when provided.');
                        return;
                    }

                    // Sort times to ensure consistent order for increment check
                    sort($availableTimes);

                    // 1. Check for duplicate times within the submitted array
                    if (count($availableTimes) !== count(array_unique($availableTimes))) {
                        $fail('The ' . $attribute . ' must not contain duplicate times.');
                        return;
                    }

                    // 2. Validate format of each time
                    foreach ($availableTimes as $time) {
                        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time)) {
                            $fail('Each available time must be in HH:MM format.');
                            return; // Stop further validation for this rule on first error
                        }
                    }

                    // 3. Check for conflicts with existing bookings for THIS service
                    // Only perform this check if we have a serviceId (i.e., we are updating)
                    if ($serviceId) {
                        $conflictingBookings = Booking::where('service_id', $serviceId)
                            ->whereIn('time', $availableTimes)
                            ->exists();

                        if ($conflictingBookings) {
                            $fail('Cannot update service. One or more of the new available times conflict with existing bookings for this service.');
                            return;
                        }
                    }
                }
            ],
            // This rule applies to each item *within* the available_times array after JSON decoding
            // It's somewhat redundant with the custom closure's format check but good for individual item validation messages.
            'available_times.*' => [
                'distinct', // Ensures times are unique within the array (handled in closure too)
                'date_format:H:i' // Ensures each time is in HH:MM format (handled in closure too)
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // This 'after' hook runs after initial validation rules pass for individual fields.
            // We need 'duration' and 'available_times' to have passed their basic validation.
            if ($validator->errors()->any()) {
                return;
            }

            $durationInput = $this->input('duration');
            $availableTimesJson = $this->input('available_times');

            // This should already be validated by the 'json' rule, but good to be safe
            $availableTimes = json_decode($availableTimesJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($availableTimes) || empty($availableTimes)) {
                // Error already handled by rules or custom closure, or available_times is empty
                return;
            }
            $durationInterval = CarbonInterval::fromString($durationInput . " minutes");
            $durationInMinutes = $durationInterval->totalMinutes;

            if ($durationInMinutes <= 0) {
                $validator->errors()->add('duration', 'Duration must result in a positive number of minutes.');
                return;
            }

            // Sort times to ensure correct interval checking
            usort($availableTimes, function ($a, $b) {
                return Carbon::createFromFormat('H:i', $a) <=> Carbon::createFromFormat('H:i', $b);
            });


            for ($i = 1; $i < count($availableTimes); $i++) {
                try {
                    $prevTime = Carbon::createFromFormat('H:i', $availableTimes[$i - 1]);
                    $currentTime = Carbon::createFromFormat('H:i', $availableTimes[$i]);
                } catch (\Exception $e) {
                    // This should have been caught by 'available_times.*' => 'date_format:H:i'
                    // or the custom closure on 'available_times'
                    $validator->errors()->add('available_times', 'One of the available times has an invalid format.');
                    return; // Stop further processing
                }

                $diffInMinutes = $currentTime->diffInMinutes($prevTime);

                if ($diffInMinutes != $durationInMinutes) {
                    $validator->errors()->add(
                        'available_times',
                        'Available times must be in increments of the specified duration (' . $durationInput . '). Mismatch found between ' . $availableTimes[$i-1] . ' and ' . $availableTimes[$i] . '.'
                    );
                    return; // Stop on first increment error
                }
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'available_times.json' => 'The available times must be a valid JSON string array.',
            'available_times.*.date_format' => 'Each available time must be in HH:MM format (e.g., 10:00).',
            'available_times.*.distinct' => 'Available times must not contain duplicate entries.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
        ];
    }
}