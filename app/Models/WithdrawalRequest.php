<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_FAILED = 3;
    const STATUS_REJECTED = 4;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'bank_account_name',
        'bank_account_number',
        'bank_ifsc',
        'gateway_payout_id',
        'processed_by',
        'processed_at',
        'admin_note',
        'failure_reason',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'decimal:2',
        'status' => 'integer',
        'processed_by' => 'integer',
        'processed_at' => 'datetime',
    ];

    protected $hidden = [
        'bank_account_number',
    ];

    /*
    |--------------------------------------------------------------------------
    | Lists
    |--------------------------------------------------------------------------
    */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    /**
     * Masked bank account number for display (e.g. XXXX1234).
     */
    public function getMaskedAccountAttribute(): string
    {
        $number = $this->bank_account_number;

        if (strlen($number) <= 4) {
            return $number;
        }

        return str_repeat('X', strlen($number) - 4) . substr($number, -4);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Checks
    |--------------------------------------------------------------------------
    */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_FAILED]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function paymentLogs()
    {
        return $this->morphMany(PaymentLog::class, 'loggable');
    }
}
