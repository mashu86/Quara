<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractualCourier extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'courier_count',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'courier_count' => 'integer',
        'date' => 'date',
    ];

    /**
     * Calculate average contractual price per courier unit.
     */
    public function getAveragePriceAttribute(): float
    {
        if ($this->courier_count > 0) {
            return round((float)$this->total_amount / (int)$this->courier_count, 2);
        }
        return 0.00;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
