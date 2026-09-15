<?php

namespace App\Services;

use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use App\Exceptions\Auth\UserBlockedException;
use App\Helpers\ActivityLogger;
use App\Models\Activity;
use App\Models\LoginHistory;
use App\Models\NurseProfile;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private const OTP_COOLDOWN_SECONDS = 60;

    private const OTP_EXPIRY_MINUTES = 10;

    private const OTP_MAX_ATTEMPTS = 3;

    public function sendOtp(string $phone)
    {
        $cooldownTime = now()->subSeconds(self::OTP_COOLDOWN_SECONDS);

        $recentOtpExists = OtpVerification::where('phone', $phone)
            ->where('created_at', '>=', $cooldownTime)
            ->exists();

        if ($recentOtpExists) {
            throw new TooManyOtpRequestsException(
                'Please wait before requesting another OTP.'
            );
        }

        OtpVerification::clearPhoneOtps($phone);

        $otp = (string) random_int(100000, 999999);

        $expiryTime = now()->addMinutes(self::OTP_EXPIRY_MINUTES);

        //store otp in database
        OtpVerification::create([
            'phone' => $phone,
            'otp' => bcrypt($otp),
            'expires_at' => $expiryTime,
            'status' => OtpVerification::STATUS_ACTIVE,
        ]);

        $isRegistered = User::where('phone', $phone)->exists();

        // Dispatch SMS
        $smsService = app(\App\Contracts\SmsServiceInterface::class);
        $message = "Welcome to VcanCares! Your OTP for verification is {$otp}. Do not share this with anyone.";
        $smsService->send($phone, $message);

        return [
            'otp' => $otp,
            'is_registered' => $isRegistered,
        ];
    }

    public function verifyOtp(
        array $data,
        string $ip,
        string $userAgent
    ) {

        $otpRecord = OtpVerification::getValidOtp(
            $data['phone']
        );

        if (!$otpRecord) {

            throw new InvalidOtpException(
                'OTP expired or invalid.'
            );
        }

        if (
            $otpRecord->attempts >=
            self::OTP_MAX_ATTEMPTS
        ) {

            $otpRecord->deactivate();

            throw new InvalidOtpException(
                'Too many invalid attempts.'
            );
        }

        $isValidOtp = Hash::check(
            $data['otp'],
            $otpRecord->otp
        );

        if (!$isValidOtp) {

            $otpRecord->incrementOtpAttempts();

            throw new InvalidOtpException(
                'Invalid OTP.'
            );
        }

        $otpRecord->markAsUsed();

        $user = User::where(
            'phone',
            $data['phone']
        )->first();

        $isNewUser = false;
        if (!$user) {
            $isNewUser = true;

            $user = DB::transaction(
                function () use ($data) {

                    $user = User::create([

                        'name' =>
                            $data['name'],

                        'phone' =>
                            $data['phone'],

                        'role' =>
                            $data['role'],

                        'status' =>
                            User::STATUS_ACTIVE,

                        'phone_verified_at' =>
                            now(),

                        'fcm_token' =>
                            $data['fcm_token'] ?? null,
                    ]);

                    // Nurse Profile
    
                    if (
                        $user->role ===
                        User::ROLE_NURSE
                    ) {

                        NurseProfile::create([

                            'user_id' =>
                                $user->id,

                            'status' =>
                                NurseProfile::STATUS_PENDING,

                            'onboarding_step' =>
                                NurseProfile::STEP_BASIC_PROFILE,
                        ]);
                    }

                    return $user;
                }
            );
        }

        if (isset($isNewUser) && $isNewUser) {
            ActivityLogger::log(
                Activity::ACTION_REGISTER,
                'User registered successfully.',
                $user
            );
        }

        if (
            $user->status ===
            User::STATUS_BLOCKED
        ) {

            throw new UserBlockedException(
                $user->blocked_reason
                ?? 'Your account has been blocked.'
            );
        }

        $user->tokens()->delete();

        $user->update([

            'last_login_at' => now(),

            'phone_verified_at' => now(),

            'fcm_token' =>
                $data['fcm_token']
                ?? $user->fcm_token,
        ]);

        $this->saveLoginHistory(
            user: $user,
            ip: $ip,
            userAgent: $userAgent
        );

        $token = $this->generateToken(
            $user
        );

        ActivityLogger::log(
            Activity::ACTION_LOGIN,
            'User logged in via API.',
            $user,
            ['ip' => $ip, 'user_agent' => $userAgent]
        );

        return [

            'token' => $token,
            'user' => $user,
        ];
    }

    public function logout(User $user)
    {
        ActivityLogger::log(
            Activity::ACTION_LOGOUT,
            'User logged out.',
            $user
        );
        $user->currentAccessToken()?->delete();
    }

    public function logoutAllDevices(User $user)
    {
        $user->tokens()->delete();
    }

    private function generateToken(User $user)
    {
        return $user->createToken('auth-token')->plainTextToken;
    }

    private function saveLoginHistory(User $user, string $ip, string $userAgent)
    {
        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'logged_in_at' => now(),
            'status' => LoginHistory::STATUS_ACTIVE,
        ]);
    }
}
