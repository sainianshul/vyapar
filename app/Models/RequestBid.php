<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RequestBid extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_SELECTED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_CANCELLED = 4;

    const COMMISSION_PERCENTAGE = 1;
    const COMMISSION_FLAT = 2;

    protected $fillable = [
        'care_request_id',
        'nurse_id',
        'nurse_amount',
        'commission_type',
        'commission_value',
        'commission_amount',
        'total_amount',
        'notes',
        'distance_km',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'care_request_id' => 'integer',
        'nurse_id' => 'integer',
        'status' => 'integer',
        'commission_type' => 'integer',
        'commission_value' => 'decimal:2',
        'nurse_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SELECTED => 'Selected',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getCommissionTypeList(): array
    {
        return [
            self::COMMISSION_PERCENTAGE => 'Percentage',
            self::COMMISSION_FLAT => 'Flat',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_SELECTED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_EXPIRED => 'secondary',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }



    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function careRequest()
    {
        return $this->belongsTo(CareRequest::class);
    }

    public function nurse()
    {
        return $this->belongsTo(NurseProfile::class, 'nurse_id');
    }
}
