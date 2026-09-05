<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'designation',
        'salary_type',
        'monthly_salary',
        'joining_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'joining_date' => 'date',
    ];

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Total Salary Earned (Total work/salary amount generated)
     */
    public function getTotalEarnedAttribute(): float
    {
        return (float) $this->salaries()->sum('amount');
    }

    /**
     * Total Salary Paid (Total actual cash payments made to employee)
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->salaryPayments()->sum('amount');
    }

    /**
     * Outstanding / Unpaid Salary (Total Earned - Total Paid)
     */
    public function getOutstandingSalaryAttribute(): float
    {
        $outstanding = $this->total_earned - $this->total_paid;
        return max(0.00, round($outstanding, 2));
    }
}
