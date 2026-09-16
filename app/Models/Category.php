<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'image',
        'description', 'sort_order', 'is_active', 'level',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'level' => 'integer',
        'product_count' => 'integer',
    ];

    // ─── Relationships ─────────────────────────────

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // ─── Scopes ────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // ─── Boot ──────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = self::generateUniqueSlug($category->name);
            $category->level = self::calculateLevel($category->parent_id);
        });

        static::updating(function ($category) {
            // Regenerate slug only if name changed
            if ($category->isDirty('name')) {
                $category->slug = self::generateUniqueSlug($category->name, $category->id);
            }
            // Recalculate level if parent changed
            if ($category->isDirty('parent_id')) {
                $category->level = self::calculateLevel($category->parent_id);
            }
        });
    }

    /**
     * Generate a unique slug, appending -2, -3, etc. if duplicate exists.
     */
    private static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
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

    /**
     * Calculate level based on parent's level.
     */
    private static function calculateLevel(?int $parentId): int
    {
        if (!$parentId) {
            return 0;
        }

        $parent = self::find($parentId);
        return $parent ? $parent->level + 1 : 0;
    }
}
