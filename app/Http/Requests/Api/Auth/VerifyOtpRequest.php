<?php

namespace App\Http\Requests\Api\Auth;

use App\Models\User;
use App\Rules\IndianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userExists = User::where(
            'phone',
            $this->phone
        )->exists();

        return [
            'phone' => [
                'required',
                new IndianPhoneNumber,
            ],

            'otp' => [
                'required',
                'digits:6',
            ],

            'name' => [
                Rule::requiredIf(!$userExists),
                'nullable',
                'string',
                'max:100',
            ],

            'role' => [
                Rule::requiredIf(!$userExists),
                'nullable',
                'integer',
                Rule::in([
                    User::ROLE_USER,
                    User::ROLE_NURSE,
                ]),
            ],

            'fcm_token' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',

            'phone.regex' => 'Enter a valid Indian mobile number.',

            'otp.required' => 'OTP is required.',

            'otp.digits' => 'OTP must be 6 digits.',

            'name.required' => 'Name is required for new users.',

            'role.required' => 'Role is required for new users.',

            'role.in' => 'Invalid role selected.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'phone' => [
                'description' => 'Valid Indian mobile number.',
                'example' => '9876543210',
            ],

            'otp' => [
                'description' => '6 digit OTP.',
                'example' => '123456',
            ],

            'name' => [
                'description' => 'Required only for new users.',
                'example' => 'Anshul',
            ],

            'role' => [
                'description' => 'Required only for new users. 1 = User, 2 = Nurse',
                'example' => 1,
            ],

            'fcm_token' => [
                'description' => 'Firebase Cloud Messaging token.',
                'example' => 'fcm_xxxxxxxxx',
            ],
        ];
    }
}