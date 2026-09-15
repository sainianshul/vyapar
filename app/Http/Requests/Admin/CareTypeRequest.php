<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CareTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'commision_type' => ['required', 'integer', 'in:0,1,2'],
            'commision_value' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:10240'], // Relaxed limits
            'status' => ['nullable', 'integer', 'in:0,1,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Care type name is required.',
            'commision_type.required' => 'Commission type is required.',
            'commision_type.in' => 'Commission type must be Percentage or Fixed.',
            'commision_value.required' => 'Commission value is required.',
            'commision_value.numeric' => 'Commission value must be a number.',
            'commision_value.max' => 'Commission percentage cannot exceed 100.',
            'image.image' => 'File must be a valid image.',
            'image.max' => 'Image must not exceed 2MB.',
        ];
    }
}