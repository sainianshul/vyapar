<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['name', 'image', 'type', 'reference_id', 'status'];

    public const TYPE_CATEGORY = 1;
    public const TYPE_PRODUCT = 2;
    public const TYPE_SELLER = 3;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $appends = ['type_name', 'banner_image'];

    public function getTypeNameAttribute()
    {
        return match ($this->type) {
            self::TYPE_CATEGORY => 'Category',
            self::TYPE_PRODUCT => 'Product',
            self::TYPE_SELLER => 'Seller',
            default => 'Unknown',
        };
    }

    public function getBannerImageAttribute()
    {
        return $this->image ? url('storage/' . $this->image) : null;
    }
}
