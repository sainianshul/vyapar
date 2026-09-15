<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Event Type Constants
    |--------------------------------------------------------------------------
    */
    const EVENT_ORDER_CREATED = 1;
    const EVENT_PAYMENT_SUCCESS = 2;
    const EVENT_PAYMENT_FAILED = 3;
    const EVENT_REFUND_INITIATED = 4;
    const EVENT_REFUND_COMPLETED = 5;
    const EVENT_PAYOUT_INITIATED = 6;
    const EVENT_PAYOUT_COMPLETED = 7;
    const EVENT_PAYOUT_FAILED = 8;
    const EVENT_WEBHOOK_RECEIVED = 9;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'gateway_name',
        'gateway_order_id',
        'gateway_payment_id',
        'gateway_payout_id',
        'gateway_refund_id',
        'gateway_signature',
        'event_type',
        'amount',
        'currency',
        'gateway_status',
        'gateway_response',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'loggable_id' => 'integer',
        'event_type' => 'integer',
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Lists
    |--------------------------------------------------------------------------
    */
    public static function getEventTypeList(): array
    {
        return [
            self::EVENT_ORDER_CREATED => 'Order Created',
            self::EVENT_PAYMENT_SUCCESS => 'Payment Success',
            self::EVENT_PAYMENT_FAILED => 'Payment Failed',
            self::EVENT_REFUND_INITIATED => 'Refund Initiated',
            self::EVENT_REFUND_COMPLETED => 'Refund Completed',
            self::EVENT_PAYOUT_INITIATED => 'Payout Initiated',
            self::EVENT_PAYOUT_COMPLETED => 'Payout Completed',
            self::EVENT_PAYOUT_FAILED => 'Payout Failed',
            self::EVENT_WEBHOOK_RECEIVED => 'Webhook Received',
        ];
    }

    public function getEventTypeTextAttribute(): string
    {
        return self::getEventTypeList()[$this->event_type] ?? 'Unknown';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships (Polymorphic)
    |--------------------------------------------------------------------------
    */
    public function loggable()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Factory Method — single point for creating log entries
    |--------------------------------------------------------------------------
    */

    /**
     * Log a payment gateway event.
     *
     * @param Model  $loggable      The model this log belongs to (Booking, WithdrawalRequest, etc.)
     * @param int    $eventType     One of the EVENT_* constants
     * @param float  $amount        Amount in rupees (not paise)
     * @param array  $gatewayData   Data from the gateway (order_id, payment_id, etc.)
     * @param array  $httpMeta      Optional HTTP metadata (ip, user_agent)
     */
    public static function record(
        Model $loggable,
        int $eventType,
        float $amount,
        array $gatewayData = [],
        array $httpMeta = []
    ): self {
        return self::create([
            'loggable_type' => get_class($loggable),
            'loggable_id' => $loggable->id,
            'gateway_name' => $gatewayData['gateway_name'] ?? config('care.payment_gateway', 'razorpay'),
            'gateway_order_id' => $gatewayData['gateway_order_id'] ?? null,
            'gateway_payment_id' => $gatewayData['gateway_payment_id'] ?? null,
            'gateway_payout_id' => $gatewayData['gateway_payout_id'] ?? null,
            'gateway_refund_id' => $gatewayData['gateway_refund_id'] ?? null,
            'gateway_signature' => $gatewayData['gateway_signature'] ?? null,
            'event_type' => $eventType,
            'amount' => round($amount, 2),
            'currency' => $gatewayData['currency'] ?? 'INR',
            'gateway_status' => $gatewayData['gateway_status'] ?? null,
            'gateway_response' => $gatewayData['gateway_raw'] ?? null,
            'ip_address' => $httpMeta['ip'] ?? null,
            'user_agent' => $httpMeta['user_agent'] ?? null,
            'created_at' => now(),
        ]);
    }
}
