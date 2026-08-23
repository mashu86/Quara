<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'background_image',
        'text_color',
        'status',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('status', 'active');
    }

    public function getBackgroundImageUrlAttribute(): string
    {
        if (!$this->background_image) {
            return asset('assets/images/logo.png');
        }

        if (str_starts_with($this->background_image, 'http://') || str_starts_with($this->background_image, 'https://') || filter_var($this->background_image, FILTER_VALIDATE_URL)) {
            return $this->background_image;
        }

        $cleanPath = ltrim(str_replace('storage/', '', $this->background_image), '/');
        return route('media.show', ['path' => $cleanPath]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
