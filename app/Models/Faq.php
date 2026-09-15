<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    protected $fillable = [
        'support_category_id',
        'question',
        'answer',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public static function getStatusList(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_DRAFT => 'warning',
            self::STATUS_INACTIVE => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'ki-check-circle',
            self::STATUS_DRAFT => 'ki-notepad-edit',
            self::STATUS_INACTIVE => 'ki-cross-circle',
            default => 'ki-information-5',
        };
    }

    public function supportCategory(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class);
    }
}
