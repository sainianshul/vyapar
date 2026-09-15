<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateCareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'care_type_id' => ['required', 'integer', 'exists:care_types,id'],
            'care_for' => ['required', 'integer', 'in:1,2'], // 1: Self, 2: Other
            'patient_name' => ['required_if:care_for,2', 'nullable', 'string', 'max:255'],
            'patient_age' => ['required_if:care_for,2', 'nullable', 'string', 'max:50'],
            'contact_phone' => ['required', 'string', 'max:15'],
            'secondary_phone' => ['nullable', 'string', 'max:15'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'pincode' => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
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
                    
                    $minHours = config('care.min_booking_notice_hours', 2);
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
