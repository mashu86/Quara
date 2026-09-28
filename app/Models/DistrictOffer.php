<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class DistrictOffer extends Model
{
    protected $fillable = ['district', 'state', 'start_date', 'end_date', 'method', 'value', 'is_active', 'created_by'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'value' => 'decimal:2', 'is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('District offers are append-only. Save a new version.'));
        static::deleting(fn () => throw new LogicException('District offer history cannot be deleted.'));
    }
}
