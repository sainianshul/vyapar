<?php

namespace App\Http\Requests\Api\Nurse\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class WorkHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'work_histories' => [
                'required',
                'array',
                'min:1',
            ],

            'work_histories.*.role_or_position' => [
                'required',
                'string',
                'max:255',
            ],

            'work_histories.*.organization_name' => [
                'required',
                'string',
                'max:255',
            ],

            'work_histories.*.location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'work_histories.*.start_date' => [
                'required',
                'date',
            ],

            'work_histories.*.end_date' => [
                'nullable',
                'date',
                'after_or_equal:work_histories.*.start_date',
            ],

            'work_histories.*.is_currently_working' => [
                'required',
                'boolean',
            ],

            'work_histories.*.description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

}