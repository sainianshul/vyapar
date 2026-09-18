<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    const TYPE_TEXT = 1;
    const TYPE_IMAGE = 2;
    const TYPE_CALLLOG = 3;
    const TYPE_SYSTEM = 4;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'media_path',
        'read_at',
    ];

    protected $casts = [
        'type' => 'integer',
        'read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'type' => $this->type,
            'body' => $this->body,
            'media_url' => $this->media_path ? asset('storage/' . $this->media_path) : null,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
