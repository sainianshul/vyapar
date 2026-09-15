<?php

$baseUrl = 'http://localhost:8000/api/v1';

function postJson($url, $data, $token = null) {
    $ch = curl_init($url);
    $payload = json_encode($data);
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: curl/7.68.0'
    ];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($result, true)];
}

function postMultipart($url, $data, $token) {
    $ch = curl_init($url);
    $headers = [
        'Accept: application/json',
        'User-Agent: curl/7.68.0',
        "Authorization: Bearer $token"
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($result, true)];
}


echo "1. Sending OTP...\n";
$sendOtp = postJson("$baseUrl/auth/send-otp", ['phone' => '8888844444']);
print_r($sendOtp);

$otp = '123456';
if (isset($sendOtp['body']['message']) && preg_match('/\((\d+)\)/', $sendOtp['body']['message'], $matches)) {
    $otp = $matches[1];
}

echo "2. Verifying OTP...\n";
$verifyOtp = postJson("$baseUrl/auth/verify-otp", [
    'phone' => '8888844444',
    'otp' => $otp,
    'name' => 'Alice Nurse',
    'role' => 2, // Nurse
    'fcm_token' => 'dummy_fcm_token_12345'
]);
print_r($verifyOtp);

$token = $verifyOtp['body']['data']['token'] ?? null;
if (!$token) {
    die("Failed to get token\n");
}

// Create dummy image
file_put_contents('dummy.jpg', base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA='));

echo "3. Basic Profile...\n";
$basicProfileData = [
    'email' => 'alice' . rand(1, 1000) . '@example.com',
    'years_of_experience' => 5,
    'license_number' => 'RN-' . rand(1000, 9999),
    'license_expiry_date' => '2028-12-31',
    'latitude' => 30.7333,
    'longitude' => 76.7794,
    'address' => 'Sector 17',
    'city' => 'Chandigarh',
    'state' => 'Punjab',
    'country' => 'India',
    'pincode' => '160017',
    'profile_photo' => new CURLFile('dummy.jpg', 'image/jpeg', 'dummy.jpg')
];

$basicProfile = postMultipart("$baseUrl/nurse/onboarding/basic-profile", $basicProfileData, $token);
print_r($basicProfile);

echo "3.1. Care Types...\n";
$careType = postJson("$baseUrl/nurse/onboarding/care-type", [
    'care_type_ids' => [1] // Assuming 1 exists
], $token);
print_r($careType);

echo "3.2. Education...\n";
$education = postJson("$baseUrl/nurse/onboarding/education", [
    'educations' => [
        [
            'degree_or_course' => 'B.Sc Nursing',
            'institute_name' => 'PGI Chandigarh',
            'field_of_study' => 'Nursing',
            'start_year' => 2018,
            'end_year' => 2022,
            'is_currently_studying' => false
        ]
    ]
], $token);
print_r($education);

echo "3.3. Work History...\n";
$workHistory = postJson("$baseUrl/nurse/onboarding/work-history", [
    'work_histories' => [
        [
            'role_or_position' => 'ICU Nurse',
            'organization_name' => 'Fortis',
            'location' => 'Chandigarh',
            'start_date' => '2022-01-01',
            'end_date' => '2023-12-31',
            'is_currently_working' => false,
            'description' => 'Worked in ICU.'
        ]
    ]
], $token);
print_r($workHistory);

echo "3.4. Documents...\n";
$docsData = [
    'marksheet_10_document' => new CURLFile('dummy.jpg', 'image/jpeg', 'dummy.jpg'),
    'marksheet_12_document' => new CURLFile('dummy.jpg', 'image/jpeg', 'dummy.jpg'),
    'aadhar_document' => new CURLFile('dummy.jpg', 'image/jpeg', 'dummy.jpg'),
    'pan_document' => new CURLFile('dummy.jpg', 'image/jpeg', 'dummy.jpg'),
    'nursing_certificate_document' => new CURLFile('dummy.jpg', 'image/jpeg', 'dummy.jpg'),
    'license_document' => new CURLFile('dummy.jpg', 'image/jpeg', 'dummy.jpg'),
    'degree_document' => new CURLFile('dummy.jpg', 'image/jpeg', 'dummy.jpg')
];
$documents = postMultipart("$baseUrl/nurse/onboarding/documents", $docsData, $token);
print_r($documents);

echo "4. Submit For Review...\n";
$submit = postJson("$baseUrl/nurse/onboarding/submit", [], $token);
print_r($submit);

echo "Done!\n";
