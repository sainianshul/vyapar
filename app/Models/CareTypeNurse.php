<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CareTypeNurse extends Model
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $table = 'care_type_nurse';

    protected $fillable = [
        'nurse_id',
        'care_type_id',
        'status',
    ];

    protected $casts = [
        'nurse_id' => 'integer',
        'care_type_id' => 'integer',
        'status' => 'integer',
    ];

    public static function getStatusList(): array
    {
        return [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
        ];
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function nurse()
    {
        return $this->belongsTo(NurseProfile::class, 'nurse_id');
    }

    public function careType()
    {
        return $this->belongsTo(CareType::class, 'care_type_id');
    }
}