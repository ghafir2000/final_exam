<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator; // Import Validator
use Carbon\Carbon;
use Carbon\CarbonInterval;

class CreateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Your existing authorization logic
        return $this->input('servicable_id') == auth()->user()->userable_id &&
               $this->input('servicable_type') == auth()->user()->userable_type;
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
            'service_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'name' => 'required|string|max:255',
            'animal_id' => 'required|integer|exists:animals,id', // Added for the animal selection
            'price' => 'required|numeric|min:0', // Price should be numeric
            'description' => 'required|string',
            'duration' => [
                'required',
                'string', // The input from the form will be a string (e.g., "30") representing minutes
                          // We'll convert this to a parseable duration string in withValidator
                function ($attribute, $value, $fail) {
                    if (!is_numeric($value) || intval($value) <= 0) {
                        $fail('The ' . $attribute . ' must be a positive number of minutes.');
                    }
                    // Further validation (parsing as CarbonInterval) happens in withValidator
                }
            ],
            'servicable_id' => 'required|integer', // Made required as per your authorize logic
            'servicable_type' => 'required|string',  // Made required as per your authorize logic
            'available_times' => [
                'required',
                'json', // Ensures the input is a valid JSON string
                function ($attribute, $value, $fail) {
                    $availableTimes = json_decode($value, true);

                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($availableTimes)) {
                        $fail('The ' . $attribute . ' must be a valid JSON array of times.');
                        return;
                    }

                    if (empty($availableTimes)) {
                        $fail('The ' . $attribute . ' field must contain at least one time slot.');
                        return;
                    }

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
                }
            ],
            // This rule applies to each item *within* the available_times array after JSON decoding.
            // It's somewhat redundant with the custom closure's format check but good for individual item validation messages.
            'available_times.*' => [
                'distinct', // Ensures times are unique (also handled in closure)
                'date_format:H:i' // Ensures HH:MM format (also handled in closure)
            ],
            'breeds' => 'nullable|array',
            'breeds.*' => 'nullable|integer|exists:breeds,id', // Validate each breed ID
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

            $durationInputMinutes = $this->input('duration'); // e.g., "30"
            $availableTimesJson = $this->input('available_times');

            // This should already be validated by the 'json' rule, but good to be safe
            $availableTimes = json_decode($availableTimesJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($availableTimes) || empty($availableTimes)) {
                // Error already handled by rules or custom closure, or available_times is empty
                return;
            }

            // Convert numeric minutes to a string CarbonInterval can parse
            $durationStringForInterval = $durationInputMinutes . " minutes";

            try {
                $durationInterval = CarbonInterval::fromString($durationStringForInterval);
                $durationInMinutes = $durationInterval->totalMinutes;

                if ($durationInMinutes <= 0) { // Double check from the 'duration' field's rule
                    $validator->errors()->add('duration', 'Duration must result in a positive number of minutes.');
                    return;
                }
            } catch (\Exception $e) {
                // This error should have been caught by the 'duration' field's rule
                // or if the conversion to "$durationInputMinutes minutes" fails for some reason
                $validator->errors()->add('duration', 'The duration format is invalid or cannot be processed.');
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
                        'Available times must be in increments of the specified duration (' . $durationInputMinutes . ' minutes). Mismatch found between ' . $availableTimes[$i-1] . ' and ' . $availableTimes[$i] . '.'
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
            'animal_id.required' => 'Please select an animal.',
            'animal_id.exists' => 'The selected animal is invalid.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'duration.required' => 'The duration field is required.',
            'duration.string' => 'The duration must be a valid number of minutes.', // Adjusted
            'available_times.required' => 'Please add at least one available time slot.',
            'available_times.json' => 'The available times data is not in the correct format.',
            'available_times.*.date_format' => 'Each available time must be in HH:MM format (e.g., 10:00).',
            'available_times.*.distinct' => 'Available times must not contain duplicate entries.',
            'breeds.*.exists' => 'One or more of the selected breeds is invalid.',
            'servicable_id.required' => 'The serviceable ID is required.',
            'servicable_type.required' => 'The serviceable type is required.',
        ];
    }
}