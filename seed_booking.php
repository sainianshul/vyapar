<?php
// Load Laravel environment
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
putenv('DB_HOST=127.0.0.1');
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CareRequest;
use App\Models\RequestBid;
use App\Models\Booking;
use App\Models\BookingSession;
use Illuminate\Support\Str;

try {
    // Find a care request with a bid
    $bid = RequestBid::with(['careRequest', 'nurse.user'])->first();
    
    if (!$bid) {
        die("No bids found. Please ensure a bid exists first.\n");
    }

    $careRequest = $bid->careRequest;

    if (Booking::where('care_request_id', $careRequest->id)->exists()) {
        echo "A booking already exists for this care request. Creating another one for demonstration.\n";
    }

    $referenceId = 'BKG' . strtoupper(Str::random(6));

    $booking = Booking::create([
        'reference_id' => $referenceId,
        'care_request_id' => $careRequest->id,
        'bid_id' => $bid->id,
        'user_id' => $careRequest->user_id,
        'nurse_id' => $bid->nurse_id,
        'nurse_amount' => $bid->nurse_amount,
        'commission_type' => $bid->commission_type,
        'commission_value' => $bid->commission_value,
        'commission_amount' => $bid->commission_amount,
        'total_amount' => $bid->total_amount,
        
        'start_date' => \Carbon\Carbon::today(),
        'end_date' => \Carbon\Carbon::today()->addDays(7),
        'total_sessions' => 7,
        'completed_sessions' => 2,
        
        'status' => Booking::STATUS_ACTIVE,
        'payment_status' => Booking::PAYMENT_PAID,
        'payment_method' => Booking::PAY_METHOD_GATEWAY,
        
        'gateway_order_id' => 'order_' . Str::random(10),
        'gateway_payment_id' => 'pay_' . Str::random(10),
        'gateway_amount' => $bid->total_amount,
        
        'patient_name' => $careRequest->patient_name,
        'patient_age' => $careRequest->patient_age,
        'contact_phone' => $careRequest->contact_phone,
        'address' => $careRequest->address,
        'city' => $careRequest->city,
    ]);

    // Create a couple of sessions
    for ($i = 1; $i <= 7; $i++) {
        $status = ($i <= 2) ? BookingSession::STATUS_COMPLETED : BookingSession::STATUS_UPCOMING;
        
        BookingSession::create([
            'booking_id' => $booking->id,
            'session_number' => $i,
            'session_date' => \Carbon\Carbon::today()->addDays($i - 1),
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'status' => $status,
            'amount' => round($booking->total_amount / 7, 2),
            'nurse_amount' => round($booking->nurse_amount / 7, 2),
            'platform_fee' => round($booking->commission_amount / 7, 2),
            'otp' => rand(1000, 9999),
            'otp_verified_at' => ($status === BookingSession::STATUS_COMPLETED) ? now() : null,
        ]);
    }

    echo "Successfully created Booking ID: {$booking->id} (Ref: {$referenceId})\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
