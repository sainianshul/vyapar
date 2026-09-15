<?php

namespace App\Http\Requests\Api\Nurse\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class EducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'educations' => [
                'required',
                'array',
                'min:1',
            ],

            'educations.*.degree_or_course' => [
                'required',
                'string',
                'max:255',
            ],

            'educations.*.institute_name' => [
                'required',
                'string',
                'max:255',
            ],

            'educations.*.field_of_study' => [
                'nullable',
                'string',
                'max:255',
            ],

            'educations.*.start_year' => [
                'required',
                'integer',
                'digits:4',
            ],

            'educations.*.end_year' => [
                'nullable',
                'integer',
                'digits:4',
            ],

            'educations.*.is_currently_studying' => [
                'required',
                'boolean',
            ],
        ];
    }

}