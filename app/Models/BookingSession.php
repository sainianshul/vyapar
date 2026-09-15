<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BookingSession extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */
    const STATUS_UPCOMING = 0;
    const STATUS_STARTED = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_MISSED = 3;
    const STATUS_CANCELLED = 4;

    protected $fillable = [
        'booking_id',
        'session_date',
        'session_number',
        'start_time',
        'end_time',
        'start_otp',
        'end_otp',
        'otp_verified_at',
        'started_at',
        'ended_at',
        'status',
        'nurse_notes',
        'user_notes',
        'is_forced_end',
        'force_end_reason',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'session_date' => 'date',
        'session_number' => 'integer',
        'otp_verified_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => 'integer',
        'is_forced_end' => 'boolean',
    ];

    protected $hidden = [
        'start_otp',
        'end_otp',
    ];

    /*
    |--------------------------------------------------------------------------
    | Lists
    |--------------------------------------------------------------------------
    */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_UPCOMING => 'Upcoming',
            self::STATUS_STARTED => 'Started',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_MISSED => 'Missed',
            self::STATUS_CANCELLED => 'Cancelled',
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
            self::STATUS_UPCOMING => 'warning',
            self::STATUS_STARTED => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_MISSED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    /*
    |--------------------------------------------------------------------------
    | OTP Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate fresh 6-digit OTPs for this session.
     */
    public function generateOtps(): array
    {
        $startOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $endOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'start_otp' => $startOtp,
            'end_otp' => $endOtp,
            'otp_verified_at' => null,
        ]);

        return [
            'start_otp' => $startOtp,
            'end_otp' => $endOtp,
        ];
    }

    /**
     * Verify start OTP.
     */
    public function verifyStartOtp(string $otp): bool
    {
        return $this->start_otp === $otp;
    }

    /**
     * Verify end OTP.
     */
    public function verifyEndOtp(string $otp): bool
    {
        return $this->end_otp === $otp;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Checks
    |--------------------------------------------------------------------------
    */
    public function isUpcoming(): bool
    {
        return $this->status === self::STATUS_UPCOMING;
    }

    public function isStarted(): bool
    {
        return $this->status === self::STATUS_STARTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('session_date', $date);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_UPCOMING,
            self::STATUS_STARTED,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function getUserSessionArray(): array
    {
        $data = [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'session_date' => $this->session_date ? $this->session_date->format('Y-m-d') : null,
            'session_number' => $this->session_number,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'status_name' => $this->status_text,
            'otp_verified_at' => $this->otp_verified_at ? $this->otp_verified_at->toIso8601String() : null,
            'started_at' => $this->started_at ? $this->started_at->toIso8601String() : null,
            'ended_at' => $this->ended_at ? $this->ended_at->toIso8601String() : null,
            'nurse_notes' => $this->nurse_notes,
            'user_notes' => $this->user_notes,
            'is_forced_end' => $this->is_forced_end,
            'force_end_reason' => $this->force_end_reason,
        ];

        // Only include OTPs if they are visible (respects $hidden / makeVisible)
        $array = $this->toArray();
        if (array_key_exists('start_otp', $array)) {
            $data['start_otp'] = $this->start_otp;
        }
        if (array_key_exists('end_otp', $array)) {
            $data['end_otp'] = $this->end_otp;
        }

        if ($this->relationLoaded('booking') && $this->booking) {
            // Include care request and nurse details if loaded
            $data['booking'] = [
                'id' => $this->booking->id,
                'reference_id' => $this->booking->reference_id,
                'care_type_name' => $this->booking->careRequest && $this->booking->careRequest->careType ? $this->booking->careRequest->careType->name : null,
                'nurse' => $this->booking->nurse && $this->booking->nurse->user ? [
                    'id' => $this->booking->nurse->id,
                    'name' => $this->booking->nurse->user->name,
                    'profile_photo' => $this->booking->nurse->user->profile_photo,
                ] : null,
            ];
        }

        return $data;
    }

    public function getNurseSessionArray(): array
    {
        $data = [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'session_date' => $this->session_date ? $this->session_date->format('Y-m-d') : null,
            'session_number' => $this->session_number,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'status_name' => $this->status_text,
            'started_at' => $this->started_at ? $this->started_at->toIso8601String() : null,
            'ended_at' => $this->ended_at ? $this->ended_at->toIso8601String() : null,
            'nurse_notes' => $this->nurse_notes,
            'user_notes' => $this->user_notes,
            'is_forced_end' => $this->is_forced_end,
            'force_end_reason' => $this->force_end_reason,
        ];

        if ($this->relationLoaded('booking') && $this->booking) {
            $data['booking'] = [
                'id' => $this->booking->id,
                'reference_id' => $this->booking->reference_id,
                'patient_name' => $this->booking->patient_name,
                'address' => $this->booking->address,
                'city' => $this->booking->city,
                'state' => $this->booking->state,
                'pincode' => $this->booking->pincode,
                'latitude' => $this->booking->latitude,
                'longitude' => $this->booking->longitude,
            ];
        }

        return $data;
    }
}

