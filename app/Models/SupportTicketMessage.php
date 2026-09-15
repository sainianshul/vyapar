<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_admin',
        'read_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_admin' => 'boolean',
        'read_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /**
     * Scope to get unread messages (not read by the counterpart).
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to get messages created after a given timestamp (for polling).
     */
    public function scopeAfter($query, string $timestamp)
    {
        return $query->where('created_at', '>', $timestamp);
    }

    // ─── Accessors ───────────────────────────────────────────────

    /**
     * Get full URLs for attachments instead of raw storage paths.
     */
    public function getAttachmentUrlsAttribute(): array
    {
        if (empty($this->attachments)) {
            return [];
        }

        return array_map(function ($path) {
            return Storage::url($path);
        }, $this->attachments);
    }
}
