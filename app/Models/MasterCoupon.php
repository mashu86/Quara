<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCoupon extends Model
{
    protected $fillable = ['code', 'name', 'discount_type', 'discount_value', 'minimum_purchase', 'starts_at', 'ends_at', 'usage_limit', 'used_count', 'allow_with_offer', 'all_categories', 'status'];
    protected $casts = ['discount_value' => 'decimal:2', 'minimum_purchase' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'allow_with_offer' => 'boolean', 'all_categories' => 'boolean', 'status' => 'boolean'];

    public function categories() { return $this->belongsToMany(Category::class, 'master_coupon_category'); }
    public function orders() { return $this->hasMany(Order::class); }
}
