<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNurseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Remove empty education rows
        if ($this->has('educations') && is_array($this->educations)) {
            $educations = array_filter($this->educations, function ($edu) {
                return !empty($edu['degree_name']) || !empty($edu['institution_name']) || !empty($edu['start_date']);
            });
            $this->merge(['educations' => count($educations) > 0 ? $educations : null]);
        }

        // Remove empty experience rows
        if ($this->has('experiences') && is_array($this->experiences)) {
            $experiences = array_filter($this->experiences, function ($exp) {
                return !empty($exp['designation']) || !empty($exp['hospital_name']) || !empty($exp['start_date']);
            });
            $this->merge(['experiences' => count($experiences) > 0 ? $experiences : null]);
        }
    }

    public function rules(): array
    {
        return [
            // User Table fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'min:10', 'max:15', 'regex:/^[0-9+]+$/'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            
            // Notification / Auto Approve
            'auto_approve' => ['nullable', 'boolean'],

            // NurseProfile Table fields
            'is_available' => ['required', 'boolean'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_phone' => ['nullable', 'string', 'min:10', 'max:15', 'regex:/^[0-9+]+$/'],
            
            // Location
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // Availability
            'available_from' => ['nullable', 'date_format:H:i'],
            'available_to' => ['nullable', 'date_format:H:i', 'after:available_from'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['integer', Rule::in([0, 1, 2, 3, 4, 5, 6])],

            // Care Types Relation
            'care_types' => ['required', 'array', 'min:1'],
            'care_types.*' => ['integer', Rule::exists('care_types', 'id')],

            // Documents
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],

            // Educations
            'educations' => ['nullable', 'array'],
            'educations.*.degree_name' => ['required', 'string', 'max:255'],
            'educations.*.institution_name' => ['required', 'string', 'max:255'],
            'educations.*.start_date' => ['required', 'date'],
            'educations.*.end_date' => ['nullable', 'date', 'after_or_equal:educations.*.start_date'],

            // Experiences
            'experiences' => ['nullable', 'array'],
            'experiences.*.designation' => ['required', 'string', 'max:255'],
            'experiences.*.hospital_name' => ['required', 'string', 'max:255'],
            'experiences.*.start_date' => ['required', 'date'],
            'experiences.*.end_date' => ['nullable', 'date', 'after_or_equal:experiences.*.start_date'],
            'experiences.*.is_currently_working' => ['nullable', 'boolean'],
        ];
    }
}
