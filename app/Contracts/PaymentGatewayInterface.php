<?php

namespace App\Contracts;

/**
 * Contract for all payment gateway implementations.
 *
 * Any payment provider (Razorpay, Stripe, PayU, etc.) must implement
 * this interface. The rest of the application talks ONLY to this interface,
 * never directly to a gateway SDK.
 *
 * Swap providers by changing the binding in AppServiceProvider — zero code changes elsewhere.
 */
interface PaymentGatewayInterface
{
    /**
     * Create a payment order on the gateway.
     *
     * @param int    $amountInPaise  Amount in smallest currency unit (paise for INR)
     * @param string $currency       ISO 4217 currency code (e.g. 'INR')
     * @param string $receiptId      Unique receipt identifier (booking reference_id)
     * @param array  $metadata       Additional data to attach to the order
     * @return array ['order_id' => string, 'amount' => int, 'currency' => string, 'gateway_raw' => array]
     */
    public function createOrder(int $amountInPaise, string $currency, string $receiptId, array $metadata = []): array;

    /**
     * Verify a payment after the user completes it on the frontend.
     *
     * @param string $orderId    Gateway order ID
     * @param string $paymentId  Gateway payment ID
     * @param string $signature  Gateway signature for verification
     * @return bool  True if signature is valid
     */
    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool;

    /**
     * Fetch payment details from the gateway.
     *
     * @param string $paymentId  Gateway payment ID
     * @return array ['status' => string, 'amount' => int, 'currency' => string, 'method' => string, 'gateway_raw' => array]
     */
    public function fetchPayment(string $paymentId): array;

    /**
     * Initiate a payout (bank transfer) to a beneficiary.
     *
     * @param int    $amountInPaise   Amount in smallest currency unit
     * @param string $currency        ISO 4217 currency code
     * @param string $beneficiaryName Account holder name
     * @param string $accountNumber   Bank account number
     * @param string $ifscCode        IFSC code
     * @param string $referenceId     Unique payout reference
     * @return array ['payout_id' => string, 'status' => string, 'gateway_raw' => array]
     */
    public function createPayout(int $amountInPaise, string $currency, string $beneficiaryName, string $accountNumber, string $ifscCode, string $referenceId): array;

    /**
     * Fetch payout status from the gateway.
     *
     * @param string $payoutId  Gateway payout ID
     * @return array ['status' => string, 'gateway_raw' => array]
     */
    public function fetchPayout(string $payoutId): array;

    /**
     * Initiate a refund on the gateway (for gateway-paid portion only).
     *
     * @param string $paymentId      Original payment ID
     * @param int    $amountInPaise  Refund amount in smallest currency unit
     * @param string $reason         Refund reason
     * @return array ['refund_id' => string, 'status' => string, 'gateway_raw' => array]
     */
    public function createRefund(string $paymentId, int $amountInPaise, string $reason): array;
}
