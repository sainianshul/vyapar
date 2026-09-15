<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\Payment\PaymentFailedException;
use App\Exceptions\Payment\InvalidPaymentAmountException;
use App\Exceptions\Booking\InvalidBookingStateException;
use App\Models\Booking;
use App\Models\PaymentLog;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
     * Create a gateway payment order (Razorpay/Stripe) for the gateway portion.
     */
    private function createGatewayOrder(Booking $booking, float $gatewayAmount): array
    {
        // Convert to paise (smallest currency unit) — avoids floating point at gateway boundary
        $amountInPaise = (int) round($gatewayAmount * 100);

        try {
            $orderResult = $this->gateway->createOrder(
                $amountInPaise,
                'INR',
                $booking->reference_id,
                [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                ]
            );
        } catch (\Throwable $e) {
            PaymentLog::record($booking, PaymentLog::EVENT_PAYMENT_FAILED, $gatewayAmount, [
                'gateway_status' => 'order_creation_failed',
                'gateway_raw' => ['error' => $e->getMessage()],
            ]);

            Log::error('Gateway order creation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            throw new PaymentFailedException('Unable to create payment order. Please try again.', 502);
        }

        $gatewayOrderId = $orderResult['order_id'];

        // Save order info on booking
        $booking->update([
            'gateway_order_id' => $gatewayOrderId,
            'wallet_amount_used' => 0,
            'gateway_amount' => $gatewayAmount,
        ]);

        // Log order creation
        PaymentLog::record($booking, PaymentLog::EVENT_ORDER_CREATED, $gatewayAmount, [
            'gateway_order_id' => $gatewayOrderId,
            'gateway_status' => 'created',
            'gateway_raw' => $orderResult['gateway_raw'] ?? $orderResult,
        ]);

        return [
            'payment_completed' => false,
            'payment_method' => 'gateway',
            'wallet_amount_used' => 0,
            'gateway_amount' => $gatewayAmount,
            'gateway_order_id' => $gatewayOrderId,
            'gateway_currency' => 'INR',
            'booking_reference' => $booking->reference_id,
        ];
    }
}
