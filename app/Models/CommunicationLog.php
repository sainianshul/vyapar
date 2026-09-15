<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'channel',
        'type',
        'destination',
        'content',
        'status',
        'error_message',
    ];

    /**
     * Get the parent notifiable model (user, nurse, etc.).
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
