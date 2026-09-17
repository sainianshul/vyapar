<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id', 'image_path', 'sort_order', 'is_primary'
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return filter_var($this->image_path, FILTER_VALIDATE_URL) 
            ? $this->image_path 
            : asset('storage/' . $this->image_path);
    }

    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'url' => filter_var($this->image_path, FILTER_VALIDATE_URL) 
                ? $this->image_path 
                : asset('storage/' . $this->image_path),
            'sort_order' => $this->sort_order,
            'is_primary' => $this->is_primary,
        ];
    }
}
