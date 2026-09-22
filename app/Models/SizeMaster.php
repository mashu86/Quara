<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(SizeMasterRow::class)->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
