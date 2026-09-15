<?php

namespace App\Http\Requests\Api\Nurse\Onboarding;

use App\Models\NurseDocument;
use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nurseProfile = $this->user()->nurseProfile;

        $uploadedTypes = $nurseProfile
            ? $nurseProfile->documents()->pluck('document_type')->toArray()
            : [];

        $rule = fn(string $type) =>
            in_array($type, $uploadedTypes) ? 'nullable' : 'required';

        return [
            'aadhar_document' => [
                $rule(NurseDocument::TYPE_AADHAR),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],

            'nursing_certificate_document' => [
                $rule(NurseDocument::TYPE_NURSING_CERTIFICATE),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],

            'pan_document' => [
                $rule(NurseDocument::TYPE_PAN),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
            'marksheet_10_document' => [
                $rule(NurseDocument::TYPE_MARKSHEET_10),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
            'marksheet_12_document' => [
                $rule(NurseDocument::TYPE_MARKSHEET_12),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
            'license_document' => [
                $rule(NurseDocument::TYPE_LICENSE),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ],
            'degree_document' => [
                $rule(NurseDocument::TYPE_DEGREE),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'aadhar_document.required' => 'Aadhar document is required.',
            'nursing_certificate_document.required' => 'Nursing certificate is required.',
            'aadhar_document.mimes' => 'Aadhar must be jpg, jpeg, png or pdf.',
            'nursing_certificate_document.mimes' => 'Certificate must be jpg, jpeg, png or pdf.',
            'aadhar_document.max' => 'Aadhar file must not exceed 10MB.',
            'nursing_certificate_document.max' => 'Certificate file must not exceed 10MB.',
            'license_document.required' => 'License document is required.',
            'license_document.mimes' => 'License must be jpg, jpeg, png or pdf.',
            'license_document.max' => 'License file must not exceed 10MB.',
            'degree_document.required' => 'Degree document is required.',
            'degree_document.mimes' => 'Degree must be jpg, jpeg, png or pdf.',
            'degree_document.max' => 'Degree file must not exceed 10MB.',
        ];
    }
}