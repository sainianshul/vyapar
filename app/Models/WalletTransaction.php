<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Type Constants
    |--------------------------------------------------------------------------
    */
    const TYPE_CREDIT = 1;
    const TYPE_DEBIT = 2;

    /*
    |--------------------------------------------------------------------------
    | Reason Constants
    |--------------------------------------------------------------------------
    | Every reason represents a SPECIFIC financial event. Never reuse a reason
    | for a different purpose — add a new constant instead.
    */
    const REASON_BOOKING_PAYMENT = 1;
    const REASON_CANCELLATION_REFUND = 2;
    const REASON_NURSE_PAYOUT = 3;
    const REASON_ADMIN_CREDIT = 4;
    const REASON_ADMIN_DEBIT = 5;
    const REASON_PLATFORM_FEE = 6;
    const REASON_WITHDRAWAL = 7;
    const REASON_GATEWAY_PAYMENT_RECEIVED = 8;

    protected $fillable = [
        'wallet_id',
        'booking_id',
        'type',
        'amount',
        'balance_after',
        'reason',
        'description',
        'reference_id',
        'created_at',
    ];

    protected $casts = [
        'wallet_id' => 'integer',
        'booking_id' => 'integer',
        'type' => 'integer',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'reason' => 'integer',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Lists
    |--------------------------------------------------------------------------
    */
    public static function getTypeList(): array
    {
        return [
            self::TYPE_CREDIT => 'Credit',
            self::TYPE_DEBIT => 'Debit',
        ];
    }

    public static function getReasonList(): array
    {
        return [
            self::REASON_BOOKING_PAYMENT => 'Booking Payment',
            self::REASON_CANCELLATION_REFUND => 'Cancellation Refund',
            self::REASON_NURSE_PAYOUT => 'Nurse Payout',
            self::REASON_ADMIN_CREDIT => 'Admin Credit',
            self::REASON_ADMIN_DEBIT => 'Admin Debit',
            self::REASON_PLATFORM_FEE => 'Platform Fee',
            self::REASON_WITHDRAWAL => 'Withdrawal to Bank',
            self::REASON_GATEWAY_PAYMENT_RECEIVED => 'Gateway Payment Received',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    public function getTypeTextAttribute(): string
    {
        return self::getTypeList()[$this->type] ?? 'Unknown';
    }

    public function getReasonTextAttribute(): string
    {
        return self::getReasonList()[$this->reason] ?? 'Unknown';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
