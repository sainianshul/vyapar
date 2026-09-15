<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NurseWorkHistory extends Model
{
    use SoftDeletes;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $table = 'nurse_work_history';

    protected $fillable = [
        'nurse_id',
        'role_or_position',
        'organization_name',
        'location',
        'start_date',
        'end_date',
        'is_currently_working',
        'description',
        'status',
    ];

    protected $casts = [
        'nurse_id' => 'integer',
        'is_currently_working' => 'boolean',
        'status' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
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
}