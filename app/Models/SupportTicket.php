<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasFactory, SoftDeletes;

    const PRIORITY_LOW = 0;
    const PRIORITY_MEDIUM = 1;
    const PRIORITY_HIGH = 2;

    const STATUS_PENDING = 0;
    const STATUS_OPEN = 1;
    const STATUS_DEFERRED = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_RESOLVED = 4;
    const STATUS_OTHER = 5;

    const CAT_TECHNICAL = 'technical';
    const CAT_REFUND = 'refund';
    const CAT_CANCELLATION = 'cancellation';
    const CAT_GENERAL = 'general';
    const CAT_OTHER = 'other';

    protected $fillable = [
        'reference_id',
        'user_id',
        'category',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'priority' => 'integer',
        'status' => 'integer',
    ];

    // Boot method to auto-generate reference_id
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->reference_id)) {
                $ticket->reference_id = 'TKT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    public function isClosed()
    {
        return in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_RESOLVED, self::STATUS_DEFERRED]);
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_OPEN => 'Open',
            self::STATUS_DEFERRED => 'Deferred',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_OTHER => 'Other',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_OPEN => 'primary',
            self::STATUS_DEFERRED => 'info',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_RESOLVED => 'success',
            self::STATUS_OTHER => 'secondary',
            default => 'dark',
        };
    }

    public function getPriorityTextAttribute()
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            default => 'Unknown',
        };
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_OPEN => 'Open',
            self::STATUS_DEFERRED => 'Deferred',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_OTHER => 'Other',
        ];
    }

    public static function getCategoryList()
    {
        $legacy = [
            self::CAT_TECHNICAL => 'Technical',
            self::CAT_REFUND => 'Refund',
            self::CAT_CANCELLATION => 'Cancellation',
            self::CAT_GENERAL => 'General',
            self::CAT_OTHER => 'Other',
        ];

        // Try to fetch active categories from the database
        try {
            $categories = \App\Models\SupportCategory::where('status', 1)->pluck('name', 'name')->toArray();
            if (!empty($categories)) {
                return array_merge($legacy, $categories);
            }
        } catch (\Exception $e) {
            // Fallback during migrations/setup if table doesn't exist
        }

        // Fallback backward compatible list
        return $legacy;
    }


}
