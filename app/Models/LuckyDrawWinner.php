<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LuckyDrawWinner extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'order_date' => 'datetime',
        'selected_at' => 'datetime',
        'eligibility' => 'array',
    ];

    public function draw(): BelongsTo
    {
        return $this->belongsTo(LuckyDraw::class, 'lucky_draw_id');
    }
}
