<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderOperationExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_operation_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function operation(): BelongsTo
    {
        return $this->belongsTo(OrderOperation::class, 'order_operation_id');
    }
}
