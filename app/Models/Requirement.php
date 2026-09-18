<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    const STATUS_OPEN = 1;
    const STATUS_FULFILLED = 2;
    const STATUS_CLOSED = 3;
    const STATUS_EXPIRED = 4;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'quantity',
        'target_budget',
        'delivery_location',
        'delivery_pincode',
        'city',
        'latitude',
        'longitude',
        'status',
        'search_tags',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'target_budget' => 'decimal:2',
            'quantity' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(RequirementImage::class)->orderBy('sort_order');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($req) {
            $req->search_tags = self::generateSearchTags($req);
        });

        static::updating(function ($req) {
            if ($req->isDirty(['title', 'category_id'])) {
                $req->search_tags = self::generateSearchTags($req);
            }
        });
    }

    public static function generateSearchTags(Requirement $req): string
    {
        $tags = [$req->title];
        
        if ($req->category_id) {
            $category = Category::with('parent')->find($req->category_id);
            if ($category) {
                $tags[] = $category->name;
                if ($category->parent) {
                    $tags[] = $category->parent->name;
                }
            }
        }
        
        return implode(' ', array_filter($tags));
    }

    public static function getStatusList(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_FULFILLED => 'Fulfilled',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function toListArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'target_budget' => $this->target_budget,
            'quantity' => $this->quantity,
            'city' => $this->city,
            'status' => $this->status,
            'status_name' => $this->status_name,
            'image' => $this->relationLoaded('images') && $this->images->first()
                ? asset('storage/' . $this->images->first()->image_path)
                : null,
            'user' => $this->relationLoaded('user') && $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'profile_photo' => $this->user->profile_photo ? asset('storage/' . $this->user->profile_photo) : null,
                'city' => $this->user->city,
                'joined_at' => $this->user->created_at,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }

    public function toDetailArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'target_budget' => $this->target_budget,
            'quantity' => $this->quantity,
            'delivery_location' => $this->delivery_location,
            'delivery_pincode' => $this->delivery_pincode,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'status_name' => $this->status_name,
            'user' => $this->relationLoaded('user') && $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'profile_photo' => $this->user->profile_photo ? asset('storage/' . $this->user->profile_photo) : null,
                'city' => $this->user->city,
                'joined_at' => $this->user->created_at,
            ] : null,
            'category' => $this->relationLoaded('category') && $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'images' => $this->relationLoaded('images')
                ? $this->images->map(fn($img) => $img->toApiResponse())
                : [],
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }}
