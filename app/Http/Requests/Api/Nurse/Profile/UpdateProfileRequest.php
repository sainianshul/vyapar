<?php

namespace App\Http\Requests\Api\Nurse\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Body parameters.
     */
    public function bodyParameters(): array
    {
        return [
            'bio' => [
                'description' => 'Nurse short bio/description.',
                'example' => 'Experienced ICU nurse with 5 years of experience.',
            ],
            'available_from' => [
                'description' => 'Available start time.',
                'example' => '09:00',
            ],
            'available_to' => [
                'description' => 'Available end time.',
                'example' => '18:00',
            ],
            'available_days' => [
                'description' => 'Available working days (0=Sunday to 6=Saturday).',
                'example' => [1, 2, 3, 4, 5],
            ],
            'timezone' => [
                'description' => 'Timezone of the nurse.',
                'example' => 'Asia/Kolkata',
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'bio' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'available_from' => [
                'required',
                'date_format:H:i',
            ],
            'available_to' => [
                'required',
                'date_format:H:i',
                'after:available_from',
            ],
            'available_days' => [
                'required',
                'array',
                'min:1',
            ],
            'available_days.*' => [
                'required',
                'integer',
                Rule::in([0, 1, 2, 3, 4, 5, 6]),
            ],
            'timezone' => [
                'nullable',
                'string',
                'timezone',
            ]
        ];
    }
}
