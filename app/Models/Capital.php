<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    use HasFactory;

    protected $table = 'capitals';

    protected $fillable = [
        'name',
        'capital_date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'capital_date' => 'date',
        'amount' => 'decimal:2',
    ];
}
