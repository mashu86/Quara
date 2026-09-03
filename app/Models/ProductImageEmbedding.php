<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImageEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_image_id',
        'embedding',
        'color_histogram',
        'edge_histogram',
        'checksum',
    ];

    protected $casts = [
        'embedding' => 'array',
        'color_histogram' => 'array',
        'edge_histogram' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productImage(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class);
    }
}
