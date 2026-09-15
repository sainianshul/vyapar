<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CareRequest;
use App\Models\User;
use App\Models\CareType;
use App\Models\NurseProfile;
use App\Models\RequestBid;
use Illuminate\Support\Str;
use Carbon\Carbon;

// Find a patient
$patient = User::where('role', 'patient')->first();
if (!$patient) {
    echo "No patient found.\n";
    exit;
}

// Find a care type
$careType = CareType::first();
if (!$careType) {
    echo "No care type found.\n";
    exit;
}

// Create a rich Care Request
$request = CareRequest::create([
    'reference_id' => 'REQ-' . strtoupper(Str::random(8)),
    'user_id' => $patient->id,
    'care_type_id' => $careType->id,
    'care_for' => CareRequest::CARE_FOR_OTHER,
    'patient_name' => 'John Doe Sr.',
    'patient_age' => 78,
    'contact_phone' => '+919876543210',
    'secondary_phone' => '+919876543211',
    'latitude' => 28.7041,
    'longitude' => 77.1025,
    'address' => '123, Vasant Kunj, Block C',
    'city' => 'New Delhi',
    'state' => 'Delhi',
    'country' => 'India',
    'pincode' => '110070',
    'start_date' => Carbon::now()->addDays(2),
    'end_date' => Carbon::now()->addDays(7),
    'start_time' => '09:00:00',
    'end_time' => '17:00:00',
    'notes' => 'Patient has mild dementia and requires assistance with mobility and daily meals. Needs someone who is extremely patient and speaks Hindi fluently.',
    'status' => CareRequest::STATUS_PENDING,
    'bidding_ends_at' => Carbon::now()->addHours(24),
    'matching_attempt_level' => 2,
    'total_bids_received' => 3,
    'tip_amount' => 500.00,
    'commission_type' => $careType->commision_type,
    'commission_value' => $careType->commision_value,
]);

// Find some nurses to create bids
$nurses = NurseProfile::with('user')->take(3)->get();

foreach ($nurses as $index => $nurse) {
    RequestBid::create([
        'care_request_id' => $request->id,
        'nurse_id' => $nurse->id,
        'nurse_amount' => 1500 + ($index * 150),
        'commission_type' => RequestBid::COMMISSION_PERCENTAGE,
        'commission_value' => 10,
        'commission_amount' => (1500 + ($index * 150)) * 0.10,
        'total_amount' => (1500 + ($index * 150)) * 1.10,
        'status' => ($index === 0) ? RequestBid::STATUS_SELECTED : RequestBid::STATUS_PENDING,
        'notes' => 'I am available for these dates and have 5 years of experience with elderly care.',
        'distance_km' => 5 + $index,
        'expires_at' => Carbon::now()->addHours(24),
    ]);
}

// Update request status based on accepted bid
$request->update([
    'status' => CareRequest::STATUS_ACCEPTED
]);

echo "Care Request created successfully! Ref ID: " . $request->reference_id . "\n";
