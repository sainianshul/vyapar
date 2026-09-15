<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NurseReview extends Model
{
    protected $fillable = [
        'user_id',
        'nurse_id',
        'booking_id',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'integer',
        'user_id' => 'integer',
        'nurse_id' => 'integer',
        'booking_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nurse()
    {
        return $this->belongsTo(NurseProfile::class, 'nurse_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
