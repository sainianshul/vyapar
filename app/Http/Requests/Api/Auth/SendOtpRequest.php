<?php

namespace App\Http\Requests\Api\Auth;

use App\Rules\IndianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                new IndianPhoneNumber,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',

            'phone.regex' => 'Enter a valid Indian mobile number.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'phone' => [
                'description' => 'Valid Indian mobile number.',
                'example' => '9876543210',
            ],
        ];
    }
}