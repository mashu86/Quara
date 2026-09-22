<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeMasterRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'size_master_id',
        'size_label',
        'chest',
        'waist',
        'length',
        'sort_order',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(SizeMaster::class, 'size_master_id');
    }
}
