<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NurseDocument extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    const TYPE_AADHAR = 1;
    const TYPE_PAN = 2;
    const TYPE_MARKSHEET_10 = 3;
    const TYPE_MARKSHEET_12 = 4;
    const TYPE_NURSING_CERTIFICATE = 5;
    const TYPE_LICENSE = 6;
    const TYPE_DEGREE = 7;

    protected $fillable = [
        'nurse_id',
        'document_type',
        'file_path',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'status',
    ];

    protected $casts = [
        'nurse_id' => 'integer',
        'document_type' => 'integer',
        'reviewed_by' => 'integer',
        'status' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public static function getDocumentTypeList(): array
    {
        return [
            self::TYPE_AADHAR => 'Aadhar',
            self::TYPE_PAN => 'PAN',
            self::TYPE_MARKSHEET_10 => '10th Marksheet',
            self::TYPE_MARKSHEET_12 => '12th Marksheet',
            self::TYPE_NURSING_CERTIFICATE => 'Nursing Certificate',
            self::TYPE_LICENSE => 'License',
            self::TYPE_DEGREE => 'Degree',
        ];
    }

    public function getStatusTextAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function getDocumentTypeTextAttribute(): string
    {
        return self::getDocumentTypeList()[$this->document_type] ?? 'Unknown';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function nurse()
    {
        return $this->belongsTo(NurseProfile::class, 'nurse_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}