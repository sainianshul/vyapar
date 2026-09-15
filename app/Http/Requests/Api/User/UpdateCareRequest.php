<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCareRequest extends FormRequest
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
            'notes' => ['nullable', 'string', 'max:500'],
            'secondary_phone' => ['nullable', 'string', 'max:20'],
            // These core fields can be present, but might be ignored by service based on status
            'care_type_id' => ['sometimes', 'exists:care_types,id'],
            'care_for' => ['sometimes', 'integer', 'in:1,2'],
            'patient_name' => ['nullable', 'string', 'required_if:care_for,2', 'max:100'],
            'patient_age' => ['nullable', 'string', 'required_if:care_for,2', 'max:10'],
            'contact_phone' => ['sometimes', 'string', 'max:20'],
            'latitude' => ['sometimes', 'numeric'],
            'longitude' => ['sometimes', 'numeric'],
            'address' => ['sometimes', 'string', 'max:500'],
            'city' => ['sometimes', 'string', 'max:100'],
            'state' => ['sometimes', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],
            'pincode' => ['sometimes', 'string', 'max:20'],
            'start_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startDate = $this->input('start_date');
            $startTime = $this->input('start_time');

            if ($startDate && $startTime) {
                try {
                    $userTz = $this->input('timezone', 'Asia/Kolkata');
                    $shiftStart = \Carbon\Carbon::parse($startDate . ' ' . $startTime, $userTz);

                    $minHours = config('care.min_booking_notice_hours', 6);
                    $maxDays = config('care.max_booking_advance_days', 4);

                    // Earliest they can book is X hours from right now in their timezone
                    $minValidTime = now($userTz)->addHours($minHours);

                    // Latest they can book is X days from today (end of the day) in their timezone
                    $maxValidTime = now($userTz)->addDays($maxDays)->endOfDay();

                    if ($shiftStart->isBefore($minValidTime)) {
                        $validator->errors()->add(
                            'start_time',
                            "Booking must be made at least {$minHours} hours in advance. Please choose a later time."
                        );
                    }

                    if ($shiftStart->isAfter($maxValidTime)) {
                        $validator->errors()->add(
                            'start_date',
                            "You can only schedule bookings up to {$maxDays} days in advance."
                        );
                    }
                } catch (\Exception $e) {
                    // Ignore date parsing exceptions here; standard Laravel rules catch format issues.
                }
            }
        });
    }
}
