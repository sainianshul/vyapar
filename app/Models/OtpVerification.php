<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $fillable = [
        'phone',
        'otp',
        'purpose',
        'attempts',
        'is_used',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'is_used' => 'boolean',
        'status' => 'integer',
        'expires_at' => 'datetime',
    ];

    public static function getStatusList()
    {
        return [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
        ];
    }

    public function getStatusTextAttribute()
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeUnused(Builder $query): Builder
    {
        return $query->where('is_used', false);
    }

    public static function clearPhoneOtps(string $phone)
    {
        self::where('phone', $phone)
            ->delete();
    }

    public static function getValidOtp(string $phone)
    {
        return self::where('phone', $phone)
            ->where('is_used', false)
            ->where('status', self::STATUS_ACTIVE)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'status' => self::STATUS_INACTIVE,
        ]);
    }

    public function incrementOtpAttempts()
    {
        $this->increment('attempts');
    }

    public function deactivate()
    {
        $this->update([
            'status' => self::STATUS_INACTIVE,
        ]);
    }

    public static function findByPhone(
        string $phone
    ) {

        return self::where('phone', $phone)
            ->first();
    }

}