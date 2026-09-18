<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * WebRTCCallLog Model
 * Tracks audio/video call history between users.
 */
class WebRTCCallLog extends Model
{
    use HasFactory;

    protected $table = 'webrtc_call_logs';

    protected $fillable = [
        'caller_id',
        'receiver_id',
        'product_id',
        'requirement_id',
        'call_type',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
        'end_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    /* ========================
     | Relationships
     ======================== */

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class, 'requirement_id');
    }

    /* ========================
     | Scopes (Reusable Queries)
     ======================== */

    /**
     * Get all active calls (not ended/missed/rejected) for a user.
     */
    public function scopeActiveForUser($query, $userId)
    {
        return $query->whereIn('status', ['initiated', 'ringing', 'answered'])
                     ->where(function ($q) use ($userId) {
                         $q->where('caller_id', $userId)
                           ->orWhere('receiver_id', $userId);
                     });
    }

    /**
     * Get call history for a user (as caller or receiver).
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('caller_id', $userId)
              ->orWhere('receiver_id', $userId);
        });
    }

    /* ========================
     | Helper Methods
     ======================== */

    /**
     * Check if the given user is a participant in this call.
     */
    public function isParticipant($userId): bool
    {
        return $this->caller_id == $userId || $this->receiver_id == $userId;
    }

    /**
     * Get the other user's ID in this call.
     */
    public function getOtherUserId($userId): int
    {
        return $this->caller_id == $userId ? $this->receiver_id : $this->caller_id;
    }

    /**
     * Calculate and save duration when call ends.
     */
    public function endCall(string $reason = 'caller_ended'): void
    {
        $this->ended_at = now();
        $this->status = 'ended';
        $this->end_reason = $reason;

        if ($this->started_at) {
            $this->duration_seconds = abs($this->ended_at->diffInSeconds($this->started_at));
        }

        $this->save();
    }
}
