<?php

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://localhost:8000/api/v1';

echo "1. Sending OTP...\n";
$sendOtpResponse = Http::post("$baseUrl/auth/send-otp", [
    'phone' => '9999988888',
]);

echo "Response: " . $sendOtpResponse->body() . "\n\n";
$otp = '123456'; // Assuming 123456 is standard or we check the response.
$responseData = $sendOtpResponse->json();
if (isset($responseData['message']) && preg_match('/\((\d+)\)/', $responseData['message'], $matches)) {
    $otp = $matches[1];
}

echo "2. Verifying OTP (Role 2 = Nurse)...\n";
$verifyOtpResponse = Http::post("$baseUrl/auth/verify-otp", [
    'phone' => '9999988888',
    'otp' => $otp,
    'name' => 'Jane Doe',
    'role' => 2,
]);

echo "Response: " . $verifyOtpResponse->body() . "\n\n";
$token = $verifyOtpResponse->json('data.token');

if (!$token) {
    die("Failed to get token!\n");
}

echo "3. Saving Basic Profile...\n";
$basicProfileResponse = Http::withToken($token)
    ->asMultipart()
    ->post("$baseUrl/nurse/onboarding/basic-profile", [
        'email' => 'jane.nurse@example.com',
        'years_of_experience' => 5,
        'license_number' => 'RN-'.rand(1000, 9999),
        'license_expiry_date' => '2028-12-31',
        'latitude' => 30.7333,
        'longitude' => 76.7794,
        'address' => 'Sector 17',
        'city' => 'Chandigarh',
        'state' => 'Punjab',
        'country' => 'India',
        'pincode' => '160017',
    ]);

echo "Response: " . $basicProfileResponse->body() . "\n\n";

echo "4. Submitting for Review...\n";
$submitResponse = Http::withToken($token)
    ->post("$baseUrl/nurse/onboarding/submit");

echo "Response: " . $submitResponse->body() . "\n\n";

echo "Nurse created successfully via API!\n";
