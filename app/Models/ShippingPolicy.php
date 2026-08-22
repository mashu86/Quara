<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'criteria_type',
        'from_value',
        'from_operator',
        'to_value',
        'to_operator',
        'delivery_type',
        'charge_amount',
        'status',
        'priority',
    ];

    protected $casts = [
        'from_value' => 'float',
        'to_value' => 'float',
        'charge_amount' => 'float',
        'priority' => 'integer',
    ];

    /**
     * Check if given value (cart count or price) matches this policy rule.
     */
    public function matches(float $value): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Check From Condition
        $fromOk = $this->evaluateCondition($value, $this->from_operator, (float) $this->from_value);
        if (!$fromOk) {
            return false;
        }

        // Check To Condition if specified
        if ($this->to_value !== null && $this->to_operator) {
            $toOk = $this->evaluateCondition($value, $this->to_operator, (float) $this->to_value);
            if (!$toOk) {
                return false;
            }
        }

        return true;
    }

    private function evaluateCondition(float $val, string $operator, float $target): bool
    {
        return match ($operator) {
            '<' => $val < $target,
            '<=' => $val <= $target,
            '>' => $val > $target,
            '>=' => $val >= $target,
            default => true,
        };
    }
}
