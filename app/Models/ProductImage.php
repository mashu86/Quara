<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
        'sort_order',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return Setting::logoUrl();
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://') || filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        $cleanPath = ltrim(str_replace('storage/', '', $this->image_path), '/');
        return route('media.show', ['path' => $cleanPath]);
    }
}
