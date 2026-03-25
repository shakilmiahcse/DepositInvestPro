<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitDistribution extends Model
{
    protected $table = 'profit_distributions';

    protected $guarded = ['id'];

    protected $casts = [
        'total_profit'       => 'decimal:2',
        'distributed_amount' => 'decimal:2',
        'remaining_profit'   => 'decimal:2',
        'distribution_date'  => 'datetime',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class, 'investment_id');
    }

    public function details()
    {
        return $this->hasMany(ProfitDistributionDetail::class, 'profit_distribution_id');
    }
}
