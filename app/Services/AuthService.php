<?php

namespace App\Services;

use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use App\Exceptions\Auth\UserBlockedException;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

        // store otp in database (plain text)
        OtpVerification::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => $expiryTime,
            'status' => OtpVerification::STATUS_ACTIVE,
        ]);

        $isRegistered = User::where('phone', $phone)->exists();

        // TODO: Dispatch actual SMS via gateway here in production
        // $message = "Welcome to VVyaparMitra! Your OTP for verification is {$otp}. Do not share this with anyone.";
        
        return [
            'otp' => $otp,
            'is_registered' => $isRegistered,
        ];
    }

    public function verifyOtp(array $data, string $ip, string $userAgent)
    {
        $otpRecord = OtpVerification::getValidOtp($data['phone']);

        if (!$otpRecord) {
            throw new InvalidOtpException('OTP expired or invalid.');
        }

        if ($otpRecord->attempts >= self::OTP_MAX_ATTEMPTS) {
            $otpRecord->deactivate();
            throw new InvalidOtpException('Too many invalid attempts.');
        }

        $isValidOtp = ($data['otp'] === $otpRecord->otp);

        if (!$isValidOtp) {
            $otpRecord->incrementOtpAttempts();
            throw new InvalidOtpException('Invalid OTP.');
        }

        $otpRecord->markAsUsed();

        $user = User::where('phone', $data['phone'])->first();
        if (!$user) {
            $user = DB::transaction(function () use ($data) {
                return User::create([
                    'phone' => $data['phone'],
                    'role' => User::ROLE_USER, // default role for new registrations
                    'status' => User::STATUS_ACTIVE,
                    'phone_verified_at' => now(),
                    'created_by' => User::CREATED_BY_SELF,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'profile_completed_at' => null,
                ]);
            });
        }

        if ($user->status === User::STATUS_BLOCKED) {
            throw new UserBlockedException(
                $user->blocked_reason ?? 'Your account has been blocked.'
            );
        }

        $user->update([
            'last_login_at' => now(),
            'phone_verified_at' => now(),
            'location_updated_at' => (isset($data['latitude']) && isset($data['longitude'])) ? now() : $user->location_updated_at,
            'latitude' => $data['latitude'] ?? $user->latitude,
            'longitude' => $data['longitude'] ?? $user->longitude,
        ]);

        $token = $this->generateDeviceToken($user, $data, $ip, $userAgent);

        return [
            'token' => $token,
            'user' => $user,
            'is_profile_complete' => !is_null($user->profile_completed_at),
        ];
    }

    public function logout(User $user)
    {
        // Delete ONLY current device token
        $user->currentAccessToken()?->delete();
    }

    public function logoutAllDevices(User $user)
    {
        // Delete ALL tokens
        $user->tokens()->delete();
    }

    private function generateDeviceToken(User $user, array $data, string $ip, string $userAgent)
    {
        // Create token
        $tokenResult = $user->createToken('auth-token');
        
        // Update the token with device info
        $accessToken = $tokenResult->accessToken;
        $accessToken->device_id = $data['device_id'] ?? null;
        $accessToken->device_name = $data['device_name'] ?? null;
        $accessToken->device_type = $data['device_type'] ?? null;
        $accessToken->fcm_token = $data['fcm_token'] ?? null;
        $accessToken->latitude = $data['latitude'] ?? null;
        $accessToken->longitude = $data['longitude'] ?? null;
        $accessToken->ip_address = $ip;
        $accessToken->user_agent = $userAgent;
        $accessToken->save();

        return $tokenResult->plainTextToken;
    }
}
