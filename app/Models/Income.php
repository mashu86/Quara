<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'income_name',
        'income_price',
        'type',
        'selling_pieces',
        'total_income_amount',
        'income_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'income_price' => 'decimal:2',
        'total_income_amount' => 'decimal:2',
        'selling_pieces' => 'integer',
        'income_date' => 'date',
    ];

    /**
     * Scope for active incomes only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for filtering date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('income_date', [$startDate, $endDate]);
    }

    /**
     * Human-readable type label helper.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'wholesale_selling' => 'Wholesale Selling',
            'other' => 'Other Income',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
