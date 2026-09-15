<?php

namespace App\Http\Requests\Api\Support;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|integer|in:0,1,2',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Please select a category for your ticket.',
            'subject.required' => 'Please provide a subject for your ticket.',
            'description.required' => 'Please describe your issue.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Attachments must be of type: jpeg, png, jpg, gif, pdf, doc, or docx.',
            'attachments.*.max' => 'Each attachment must not exceed 5MB.',
        ];
    }
}
