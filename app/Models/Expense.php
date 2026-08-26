<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'amount',
        'category',
        'expense_date',
        'notes',
        'receipt_image',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function getExpenseNameAttribute(): ?string
    {
        return $this->title;
    }

    public function getReceiptImagesAttribute(): array
    {
        if (empty($this->receipt_image)) {
            return [];
        }
        $decoded = json_decode($this->receipt_image, true);
        if (is_array($decoded)) {
            return array_values($decoded);
        }
        return [$this->receipt_image];
    }
}
