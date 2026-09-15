<?php

namespace App\Http\Requests\Api\Nurse\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BasicProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules.
     */

    public function rules(): array
    {
        $user = $this->user();
        $nurseProfile = $user->nurseProfile;

        // Profile photo — pehli baar required, baad mein optional
        $profilePhotoRules = $user->profile_photo
            ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']
            : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        // License unique — apni profile ignore karo
        $licenseRule = Rule::unique('nurse_profiles', 'license_number');
        if ($nurseProfile) {
            $licenseRule->ignore($nurseProfile->id);
        }

        return [
            'profile_photo' => $profilePhotoRules,

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'bio' => ['nullable', 'string', 'max:1000'],
            'years_of_experience' => ['required', 'integer', 'min:0', 'max:60'],

            'license_number' => [
                'required',
                'string',
                'max:255',
                $licenseRule,
            ],

            'license_expiry_date' => ['required', 'date', 'after:today'],

            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_photo.required' => 'Profile photo is required.',
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be jpg, jpeg, png or webp.',
            'profile_photo.max' => 'Profile photo must not exceed 5MB.',
            'license_number.unique' => 'This license number is already registered.',
            'license_expiry_date.after' => 'License expiry date must be a future date.',
            'email.unique' => 'This email is already registered.',
        ];
    }

}