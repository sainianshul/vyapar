<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'image',
        'description', 'sort_order', 'is_active', 'is_featured', 'level',
    ];

    protected $casts = [
        'is_active' => 'integer',
        'is_featured' => 'integer',
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

    public function getAncestorsAttribute()
    {
        $ancestors = collect();
        $parent = $this->parent;
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }
        return $ancestors->reverse();
    }

    public function getAllChildrenIds()
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        return $ids;
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

    // ─── DataTables Helpers ───────────────────────

    public static function getStatusList(): array
    {
        return [
            1 => 'Active',
            0 => 'Inactive',
        ];
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusList()[$this->is_active] ?? 'Unknown';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    public function getStatusIconAttribute(): string
    {
        return $this->is_active ? 'ti ti-check' : 'ti ti-x';
    }

    public function getIconHtmlAttribute(): string
    {
        if (!empty($this->image)) {
            $url = filter_var($this->image, FILTER_VALIDATE_URL) 
                ? $this->image 
                : asset('storage/' . $this->image);
            return '<span class="avatar" style="background-image: url(' . e($url) . ')"></span>';
        }

        // Default image (Tabler icon for missing photo)
        return '<span class="avatar bg-light text-muted"><i class="ti ti-photo"></i></span>';
    }

    public function toApiResponse(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'image'       => $this->image ? asset('storage/' . $this->image) : null,
            'icon'        => $this->icon,
            'description' => $this->description,
            'parent_id'   => $this->parent_id,
            'level'       => $this->level,
            'sort_order'  => $this->sort_order,
            'is_featured' => $this->is_featured,
            'children'    => $this->relationLoaded('children') 
                                ? $this->children->map(fn($child) => $child->toApiResponse()) 
                                : [],
        ];
    }
}
