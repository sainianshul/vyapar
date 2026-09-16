<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    const STATUS_ACTIVE = 1;
    const STATUS_SOLD = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_BLOCKED = 4;

    const CONDITION_NEW = 1;
    const CONDITION_USED = 2;

    const UNIT_PIECE = 'per piece';
    const UNIT_DOZEN = 'per dozen';
    const UNIT_KG = 'per kg';
    const UNIT_GRAM = 'per gram';
    const UNIT_LITER = 'per liter';
    const UNIT_BOX = 'per box';
    const UNIT_PACK = 'per pack';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'price_unit',
        'is_negotiable',
        'condition',
        'quantity',
        'location',
        'city',
        'pincode',
        'latitude',
        'longitude',
        'status',
        'views_count',
        'leads_count',
        'is_featured',
        'featured_at',
        'expires_at'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'condition' => 'integer',
            'quantity' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => 'integer',
            'views_count' => 'integer',
            'leads_count' => 'integer',
            'is_featured' => 'boolean',
            'featured_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // ─── Relationships ─────────────────────────────

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    // ─── Boot ──────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = self::generateUniqueSlug($product->title);
        });

        static::updating(function ($product) {
            if ($product->isDirty('title')) {
                $product->slug = self::generateUniqueSlug($product->title, $product->id);
            }
        });
    }

    private static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 2;

        while (
            self::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }

    // ─── Helpers ───────────────────────────────────

    public static function getStatusList(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SOLD => 'Sold',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_BLOCKED => 'Blocked',
        ];
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public static function getConditionList(): array
    {
        return [
            self::CONDITION_NEW => 'New',
            self::CONDITION_USED => 'Used',
        ];
    }

    public function getConditionNameAttribute(): string
    {
        return self::getConditionList()[$this->condition] ?? 'Unknown';
    }
    
    public static function getPriceUnitsList(): array
    {
        return [
            self::UNIT_PIECE => 'Per Piece',
            self::UNIT_DOZEN => 'Per Dozen',
            self::UNIT_KG => 'Per Kg',
            self::UNIT_GRAM => 'Per Gram',
            self::UNIT_LITER => 'Per Liter',
            self::UNIT_BOX => 'Per Box',
            self::UNIT_PACK => 'Per Pack',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_SOLD => 'info',
            self::STATUS_EXPIRED => 'warning',
            self::STATUS_BLOCKED => 'danger',
            default => 'secondary',
        };
    }

    // ─── Scopes ────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    // ─── API Response ──────────────────────────────

    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'is_negotiable' => $this->is_negotiable,
            'condition' => $this->condition,
            'condition_name' => $this->condition_name,
            'quantity' => $this->quantity,
            'location' => $this->location,
            'city' => $this->city,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'status_name' => $this->status_name,
            'views_count' => $this->views_count,
            'leads_count' => $this->leads_count,
            'is_featured' => $this->is_featured,
            'seller' => $this->relationLoaded('seller') && $this->seller ? [
                'id' => $this->seller->id,
                'name' => $this->seller->name,
                'profile_photo' => $this->seller->profile_photo ? asset('storage/' . $this->seller->profile_photo) : null,
            ] : null,
            'category' => $this->relationLoaded('category') && $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'images' => $this->relationLoaded('images') 
                        ? $this->images->map(fn($img) => $img->toApiResponse()) 
                        : [],
            'created_at' => $this->created_at,
        ];
    }
}
