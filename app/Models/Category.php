<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Support\Str;

class Category extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'name', 'description', 'slug', 'icon', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            self::ensureSlug($category);
        });

        static::updating(function (self $category): void {
            if ($category->isDirty('name') || empty($category->slug)) {
                self::ensureSlug($category);
            }
        });
    }

    protected static function ensureSlug(self $category): void
    {
        $baseSlug = Str::slug($category->name ?: 'category');
        $slug = $baseSlug;
        $counter = 2;

        while (static::where('restaurant_id', $category->restaurant_id)
            ->where('slug', $slug)
            ->when($category->exists, fn ($query) => $query->where('id', '!=', $category->id))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $category->slug = $slug;
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function availableMenuItems()
    {
        return $this->hasMany(MenuItem::class)->where('is_available', true)->orderBy('sort_order');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
