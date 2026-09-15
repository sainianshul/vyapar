<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

/**
 * Dummy Payment Gateway for local development and testing.
 * Implements the gateway contract without making real API calls.
 */
class DummyPaymentGateway implements PaymentGatewayInterface
{
    public function createOrder(int $amountInPaise, string $currency, string $receiptId, array $metadata = []): array
    {
        return [
            'order_id' => 'order_dummy_' . Str::random(10),
            'amount' => $amountInPaise,
            'currency' => $currency,
            'gateway_raw' => [
                'id' => 'order_dummy_' . Str::random(10),
                'amount' => $amountInPaise,
                'status' => 'created',
                'receipt' => $receiptId,
                'notes' => $metadata,
            ]
        ];
    }

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        // For testing, we'll assume any signature that isn't exactly 'fail' is valid
        return $signature !== 'fail';
    }

    public function fetchPayment(string $paymentId): array
    {
        return [
            'status' => 'captured',
            'amount' => 100000,
            'currency' => 'INR',
            'method' => 'card',
            'gateway_raw' => [
                'id' => $paymentId,
                'status' => 'captured',
                'amount' => 100000,
            ]
        ];
    }

    public function createPayout(int $amountInPaise, string $currency, string $beneficiaryName, string $accountNumber, string $ifscCode, string $referenceId): array
    {
        return [
            'payout_id' => 'pout_dummy_' . Str::random(10),
            'status' => 'processing',
            'gateway_raw' => [
                'id' => 'pout_dummy_' . Str::random(10),
                'amount' => $amountInPaise,
                'status' => 'processing',
                'reference_id' => $referenceId,
            ]
        ];
    }

    public function fetchPayout(string $payoutId): array
    {
        return [
            'status' => 'processed',
            'gateway_raw' => [
                'id' => $payoutId,
                'status' => 'processed',
            ]
        ];
    }

    public function createRefund(string $paymentId, int $amountInPaise, string $reason): array
    {
        return [
            'refund_id' => 'rfnd_dummy_' . Str::random(10),
            'status' => 'processed',
            'gateway_raw' => [
                'id' => 'rfnd_dummy_' . Str::random(10),
                'payment_id' => $paymentId,
                'amount' => $amountInPaise,
                'status' => 'processed',
            ]
        ];
    }
}
