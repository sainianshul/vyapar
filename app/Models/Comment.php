<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public const TYPE_USER = 'user';
    public const TYPE_NURSE = 'nurse';
    public const TYPE_PATIENT = 'patient';
    public const TYPE_CARE_TYPE = 'care_type';
    public const TYPE_LOGIN_HISTORY = 'login_history';
    public const TYPE_LOGS = 'logs';
    public const TYPE_REQUEST_BID = 'request_bid';
    public const TYPE_CARE_REQUEST = 'care_request';
    public const TYPE_BOOKING = 'booking';

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'body',
        'created_by',
    ];

    /**
     * Get the parent commentable model.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the comment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
