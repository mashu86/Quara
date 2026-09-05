<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LuckyDraw extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'drawn_at' => 'datetime',
        'eligibility_checked_at' => 'datetime',
        'selection_rules' => 'array',
    ];

    public function winners(): HasMany
    {
        return $this->hasMany(LuckyDrawWinner::class)->orderBy('position');
    }
}
