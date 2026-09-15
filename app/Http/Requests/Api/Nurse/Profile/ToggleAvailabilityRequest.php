<?php

namespace App\Http\Requests\Api\Nurse\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ToggleAvailabilityRequest extends FormRequest
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
            'is_available' => [
                'description' => 'Availability status.',
                'example' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            'is_available' => [
                'required',
                'boolean',
            ],
        ];
    }
}
