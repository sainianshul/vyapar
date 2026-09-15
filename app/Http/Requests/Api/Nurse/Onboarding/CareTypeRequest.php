<?php

namespace App\Http\Requests\Api\Nurse\Onboarding;

use App\Models\CareType;
use App\Models\CareTypeNurse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CareTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [

            'care_type_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'care_type_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('care_types', 'id')->where('status', CareType::STATUS_ACTIVE),
            ],
        ];
    }
}