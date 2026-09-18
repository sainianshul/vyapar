<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    // Sources
    const SOURCE_INQUIRY_FORM = 1;
    const SOURCE_CALL = 2;
    const SOURCE_CHAT = 3;
    const SOURCE_VIEW_NUMBER = 4;

    // Temperatures
    const TEMP_COLD = 1;
    const TEMP_WARM = 2;
    const TEMP_HOT = 3;

    // Statuses
    const STATUS_NEW = 1;
    const STATUS_CONTACTED = 2;
    const STATUS_CONVERTED = 3;
    const STATUS_REJECTED = 4;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'requirement_id',
        'source',
        'temperature',
        'quantity',
        'message',
        'status',
    ];

    protected $casts = [
        'source' => 'integer',
        'temperature' => 'integer',
        'quantity' => 'integer',
        'status' => 'integer',
    ];

    // Relationships
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    // Helpers
    public static function getSourceList(): array
    {
        return [
            self::SOURCE_INQUIRY_FORM => 'Inquiry Form',
            self::SOURCE_CALL => 'Call',
            self::SOURCE_CHAT => 'Chat',
            self::SOURCE_VIEW_NUMBER => 'View Number',
        ];
    }

    public function getSourceNameAttribute(): string
    {
        return self::getSourceList()[$this->source] ?? 'Unknown';
    }

    public static function getTemperatureList(): array
    {
        return [
            self::TEMP_COLD => 'Cold',
            self::TEMP_WARM => 'Warm',
            self::TEMP_HOT => 'Hot',
        ];
    }

    public function getTemperatureNameAttribute(): string
    {
        return self::getTemperatureList()[$this->temperature] ?? 'Unknown';
    }

    public static function getStatusList(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_CONVERTED => 'Converted',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'buyer' => $this->relationLoaded('buyer') && $this->buyer ? [
                'id' => $this->buyer->id,
                'name' => $this->buyer->name,
                'profile_photo' => $this->buyer->profile_photo ? asset('storage/' . $this->buyer->profile_photo) : null,
                'city' => $this->buyer->city,
                'phone' => $this->buyer->phone,
            ] : null,
            'seller_id' => $this->seller_id,
            'product' => $this->relationLoaded('product') && $this->product ? [
                'id' => $this->product->id,
                'title' => $this->product->title,
                'primary_image' => $this->product->primaryImage ? asset('storage/' . $this->product->primaryImage->image_path) : null,
            ] : null,
            'requirement' => $this->relationLoaded('requirement') && $this->requirement ? [
                'id' => $this->requirement->id,
                'title' => $this->requirement->title,
            ] : null,
            'source' => $this->source,
            'source_name' => $this->source_name,
            'temperature' => $this->temperature,
            'temperature_name' => $this->temperature_name,
            'quantity' => $this->quantity,
            'message' => $this->message,
            'status' => $this->status,
            'status_name' => $this->status_name,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
