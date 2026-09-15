<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */
    const STATUS_PENDING_PAYMENT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELLED = 4;

    /*
    |--------------------------------------------------------------------------
    | Payment Status Constants
    |--------------------------------------------------------------------------
    */
    const PAYMENT_UNPAID = 0;
    const PAYMENT_PAID = 1;
    const PAYMENT_REFUND_INITIATED = 2;
    const PAYMENT_REFUNDED = 3;
    const PAYMENT_PARTIALLY_REFUNDED = 4;

    /*
    |--------------------------------------------------------------------------
    | Payment Method Constants
    |--------------------------------------------------------------------------
    */
    const PAY_METHOD_GATEWAY = 1;
    const PAY_METHOD_WALLET = 2;
    const PAY_METHOD_WALLET_PLUS_GATEWAY = 3;

    /*
    |--------------------------------------------------------------------------
    | Cancelled By Constants (shared with CareRequest)
    |--------------------------------------------------------------------------
    */
    const CANCELLED_BY_USER = 1;
    const CANCELLED_BY_NURSE = 2;
    const CANCELLED_BY_ADMIN = 3;
    const CANCELLED_BY_SYSTEM = 4;

    /*
    |--------------------------------------------------------------------------
    | Refund Mode Constants
    |--------------------------------------------------------------------------
    */
    const REFUND_TO_WALLET = 1;
    const REFUND_TO_BANK = 2;

    public static function getRefundModeList(): array
    {
        return [
            self::REFUND_TO_WALLET => 'Wallet',
            self::REFUND_TO_BANK => 'Bank Account',
        ];
    }

    protected $fillable = [
        'reference_id',
        'care_request_id',
        'bid_id',
        'user_id',
        'nurse_id',
        'nurse_amount',
        'commission_type',
        'commission_value',
        'commission_amount',
        'total_amount',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'total_sessions',
        'completed_sessions',
        'status',
        'payment_status',
        'payment_method',
        'gateway_order_id',
        'gateway_payment_id',
        'wallet_amount_used',
        'gateway_amount',
        'refund_amount',
        'nurse_payout_amount',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'parent_booking_id',
        'patient_name',
        'patient_age',
        'contact_phone',
        'secondary_phone',
        'care_type_name',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'care_request_id' => 'integer',
        'bid_id' => 'integer',
        'user_id' => 'integer',
        'nurse_id' => 'integer',
        'nurse_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_sessions' => 'integer',
        'completed_sessions' => 'integer',
        'status' => 'integer',
        'payment_status' => 'integer',
        'payment_method' => 'integer',
        'wallet_amount_used' => 'decimal:2',
        'gateway_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'nurse_payout_amount' => 'decimal:2',
        'cancelled_by' => 'integer',
        'cancelled_at' => 'datetime',
        'parent_booking_id' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Lists
    |--------------------------------------------------------------------------
    */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'Pending Payment',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getPaymentStatusList(): array
    {
        return [
            self::PAYMENT_UNPAID => 'Unpaid',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_REFUND_INITIATED => 'Refund Initiated',
            self::PAYMENT_REFUNDED => 'Refunded',
            self::PAYMENT_PARTIALLY_REFUNDED => 'Partially Refunded',
        ];
    }

    public static function getPaymentMethodList(): array
    {
        return [
            self::PAY_METHOD_GATEWAY => 'Payment Gateway',
            self::PAY_METHOD_WALLET => 'Wallet',
            self::PAY_METHOD_WALLET_PLUS_GATEWAY => 'Wallet + Gateway',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT => 'warning',
            self::STATUS_CONFIRMED => 'primary',
            self::STATUS_ACTIVE => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_UNPAID => 'warning',
            self::PAYMENT_PAID => 'success',
            self::PAYMENT_REFUND_INITIATED => 'warning',
            self::PAYMENT_REFUNDED => 'info',
            self::PAYMENT_PARTIALLY_REFUNDED => 'primary',
            default => 'secondary',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function getPaymentStatusTextAttribute(): string
    {
        return self::getPaymentStatusList()[$this->payment_status] ?? 'Unknown';
    }

    public function getPaymentMethodTextAttribute(): string
    {
        return self::getPaymentMethodList()[$this->payment_method] ?? 'Unknown';
    }

    /**
     * Per-session rate based on TOTAL amount (for user refund calculations).
     */
    public function getPerSessionRateAttribute(): float
    {
        if ($this->total_sessions <= 0) {
            return 0;
        }

        return round((float) $this->total_amount / $this->total_sessions, 2);
    }

    /**
     * Per-session rate based on NURSE amount (for nurse payout calculations).
     */
    public function getNursePerSessionRateAttribute(): float
    {
        if ($this->total_sessions <= 0) {
            return 0;
        }

        return round((float) $this->nurse_amount / $this->total_sessions, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Checks
    |--------------------------------------------------------------------------
    */
    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaid(): bool
    {
        return in_array($this->payment_status, [
            self::PAYMENT_PAID,
            self::PAYMENT_PARTIALLY_REFUNDED,
        ]);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_CONFIRMED,
            self::STATUS_ACTIVE,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForNurse(Builder $query, int $nurseId): Builder
    {
        return $query->where('nurse_id', $nurseId);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function careRequest()
    {
        return $this->belongsTo(CareRequest::class);
    }

    public function bid()
    {
        return $this->belongsTo(RequestBid::class, 'bid_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nurse()
    {
        return $this->belongsTo(NurseProfile::class, 'nurse_id');
    }

    public function sessions()
    {
        return $this->hasMany(BookingSession::class);
    }

    public function parentBooking()
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    public function extensions()
    {
        return $this->hasMany(Booking::class, 'parent_booking_id');
    }

    public function paymentLogs()
    {
        return $this->morphMany(PaymentLog::class, 'loggable');
    }

    public function getUserBookingArray(): array
    {
        return [
            'id' => $this->id,
            'reference_id' => $this->reference_id,
            'user_id' => $this->user_id,
            'nurse_id' => $this->nurse_id,
            'total_amount' => $this->total_amount,
            'start_date' => $this->start_date ? $this->start_date->toIso8601String() : null,
            'end_date' => $this->end_date ? $this->end_date->toIso8601String() : null,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_sessions' => $this->total_sessions,
            'completed_sessions' => $this->completed_sessions,
            'status' => $this->status,
            'status_name' => $this->status_text,
            'payment_status' => $this->payment_status,
            'payment_status_name' => $this->payment_status_text,
            'payment_method' => $this->payment_method,
            'payment_method_name' => $this->payment_method_text,
            'cancelled_by' => $this->cancelled_by,
            'cancelled_at' => $this->cancelled_at ? $this->cancelled_at->toIso8601String() : null,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'nurse' => $this->nurse && $this->nurse->user ? [
                'id' => $this->nurse->id,
                'name' => $this->nurse->user->name,
                'profile_photo' => $this->nurse->user->profile_photo,
            ] : null,
        ];
    }

    public function getUserBookingDetailArray(): array
    {
        $data = $this->getUserBookingArray();
        
        $data['care_request'] = [
            'id' => $this->careRequest ? $this->careRequest->id : null,
            'reference_id' => $this->careRequest ? $this->careRequest->reference_id : null,
            'patient_name' => $this->patient_name ?? ($this->careRequest ? $this->careRequest->patient_name : null),
            'patient_age' => $this->patient_age ?? ($this->careRequest ? $this->careRequest->patient_age : null),
            'contact_phone' => $this->contact_phone ?? ($this->careRequest ? $this->careRequest->contact_phone : null),
            'secondary_phone' => $this->secondary_phone ?? ($this->careRequest ? $this->careRequest->secondary_phone : null),
            'care_type_name' => $this->care_type_name ?? ($this->careRequest && $this->careRequest->careType ? $this->careRequest->careType->name : null),
            'address' => $this->address ?? ($this->careRequest ? $this->careRequest->address : null),
            'city' => $this->city ?? ($this->careRequest ? $this->careRequest->city : null),
            'state' => $this->state ?? ($this->careRequest ? $this->careRequest->state : null),
            'country' => $this->country ?? ($this->careRequest ? $this->careRequest->country : null),
            'pincode' => $this->pincode ?? ($this->careRequest ? $this->careRequest->pincode : null),
            'latitude' => $this->latitude ?? ($this->careRequest ? $this->careRequest->latitude : null),
            'longitude' => $this->longitude ?? ($this->careRequest ? $this->careRequest->longitude : null),
        ];

        $data['sessions'] = $this->relationLoaded('sessions') && $this->sessions ? $this->sessions->map(function($session) {
            return $session->getUserSessionArray();
        })->toArray() : [];

        return $data;
    }

    public function getNurseBookingArray(): array
    {
        return [
            'id' => $this->id,
            'reference_id' => $this->reference_id,
            'user_id' => $this->user_id,
            'nurse_amount' => $this->nurse_amount,
            'start_date' => $this->start_date ? $this->start_date->toIso8601String() : null,
            'end_date' => $this->end_date ? $this->end_date->toIso8601String() : null,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_sessions' => $this->total_sessions,
            'completed_sessions' => $this->completed_sessions,
            'status' => $this->status,
            'status_name' => $this->status_text,
            'patient_name' => $this->patient_name ?? ($this->careRequest ? $this->careRequest->patient_name : null),
            'patient_age' => $this->patient_age ?? ($this->careRequest ? $this->careRequest->patient_age : null),
            'contact_phone' => $this->contact_phone ?? ($this->careRequest ? $this->careRequest->contact_phone : null),
            'secondary_phone' => $this->secondary_phone ?? ($this->careRequest ? $this->careRequest->secondary_phone : null),
            'care_type_name' => $this->care_type_name ?? ($this->careRequest && $this->careRequest->careType ? $this->careRequest->careType->name : null),
            'address' => $this->address ?? ($this->careRequest ? $this->careRequest->address : null),
            'city' => $this->city ?? ($this->careRequest ? $this->careRequest->city : null),
            'state' => $this->state ?? ($this->careRequest ? $this->careRequest->state : null),
            'country' => $this->country ?? ($this->careRequest ? $this->careRequest->country : null),
            'pincode' => $this->pincode ?? ($this->careRequest ? $this->careRequest->pincode : null),
            'latitude' => $this->latitude ?? ($this->careRequest ? $this->careRequest->latitude : null),
            'longitude' => $this->longitude ?? ($this->careRequest ? $this->careRequest->longitude : null),
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}

