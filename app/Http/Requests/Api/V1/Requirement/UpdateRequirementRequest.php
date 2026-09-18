<?php

namespace App\Http\Requests\Api\V1\Requirement;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequirementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'target_budget' => 'nullable|numeric|min:0',
            'delivery_location' => 'nullable|string|max:255',
            'delivery_pincode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'integer',
            'new_images' => 'nullable|array|max:5',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
