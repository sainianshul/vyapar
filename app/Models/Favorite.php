<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    const TYPE_PRODUCT = 1;
    const TYPE_REQUIREMENT = 2;
    const TYPE_SELLER = 3;

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
    ];

    /**
     * Map integer constants to Model classes
     */
    public static function getModelClassForType(int $type): ?string
    {
        return match ($type) {
            self::TYPE_PRODUCT => Product::class,
            self::TYPE_REQUIREMENT => Requirement::class,
            self::TYPE_SELLER => User::class,
            default => null,
        };
    }

    /**
     * Polymorphic relation
     */
    public function model()
    {
        return $this->morphTo();
    }
}
