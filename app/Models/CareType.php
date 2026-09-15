<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareType extends Model
{
    use SoftDeletes;

    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'created_by',
        'status',
        'commision_type',
        'commision_value',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'status' => 'integer',
    ];

    public static function getStatusList(): array
    {
        return [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DRAFT => 'Draft',
        ];
    }

    const COMMISION_TYPE_FIXED_PER_DAY = 0;
    const COMMISION_TYPE_PERCENT = 1;
    const COMMISION_TYPE_FLAT_FIXED = 2;

    public static function getCommisionTypeList(): array
    {
        return [
            self::COMMISION_TYPE_FIXED_PER_DAY => 'Fixed Per Day',
            self::COMMISION_TYPE_PERCENT => 'Percent',
            self::COMMISION_TYPE_FLAT_FIXED => 'Fixed Total',
        ];
    }

    public function getCommissionTextAttribute(): string
    {
        if ($this->commision_type === self::COMMISION_TYPE_PERCENT) {
            return $this->commision_value . '%';
        } elseif ($this->commision_type === self::COMMISION_TYPE_FLAT_FIXED) {
            return '₹' . $this->commision_value . ' flat';
        }
        return '₹' . $this->commision_value . ' / day';
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function nurses()
    {
        return $this->belongsToMany(
            NurseProfile::class,
            'care_type_nurse',
            'care_type_id',
            'nurse_id'
        );
    }

    public function careRequests()
    {
        return $this->hasMany(CareRequest::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = str($model->name)->slug();
            }
        });
    }
}