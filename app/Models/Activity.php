<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'properties' => 'array',
        'action_type' => 'integer',
    ];

    public const ACTION_REGISTER = 1;
    public const ACTION_LOGIN = 2;
    public const ACTION_LOGOUT = 3;
    public const ACTION_ONBOARDING_SUBMIT = 4;
    public const ACTION_APPROVED = 5;
    public const ACTION_REJECTED = 6;
    public const ACTION_UPDATED = 7;

    public function subject()
    {
        return $this->morphTo();
    }

    public function causer()
    {
        return $this->morphTo();
    }
}
