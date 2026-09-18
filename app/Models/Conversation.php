<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use SoftDeletes;

    const STATUS_ACTIVE = 1;
    const STATUS_ARCHIVED = 2;
    const STATUS_BLOCKED = 3;

    protected $fillable = [
        'product_id',
        'requirement_id',
        'buyer_id',
        'seller_id',
        'last_message_at',
        'buyer_unread_count',
        'seller_unread_count',
        'status',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'buyer_unread_count' => 'integer',
        'seller_unread_count' => 'integer',
        'status' => 'integer',
    ];

    // Relationships
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Helpers
    public function markAsReadFor(int $userId)
    {
        if ($this->buyer_id === $userId) {
            $this->update(['buyer_unread_count' => 0]);
        } elseif ($this->seller_id === $userId) {
            $this->update(['seller_unread_count' => 0]);
        }
    }

    public function incrementUnreadForOther(int $senderId)
    {
        if ($this->buyer_id === $senderId) {
            $this->increment('seller_unread_count');
        } elseif ($this->seller_id === $senderId) {
            $this->increment('buyer_unread_count');
        }
    }
}
