<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'investments';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invested_amount' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'start_date'      => 'date',
        'end_date'        => 'date',
    ];

    public function transactions()
    {
        return $this->hasMany(InvestmentTransaction::class, 'investment_id');
    }

    public function getTotalInvestedAttribute()
    {
        return (float) $this->transactions()
            ->where('type', 'invest')
            ->sum('amount');
    }

    public function getTotalReturnAttribute()
    {
        return (float) $this->transactions()
            ->where('type', 'return')
            ->sum('amount');
    }

    public function getTotalExpenseAttribute()
    {
        return (float) $this->transactions()
            ->where('type', 'expense')
            ->sum('amount');
    }

    public function getNetProfitAttribute()
    {
        return $this->total_return - $this->total_invested - $this->total_expense;
    }
}
