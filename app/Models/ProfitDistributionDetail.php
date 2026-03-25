<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitDistributionDetail extends Model
{
    protected $table = 'profit_distribution_details';

    protected $guarded = ['id'];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'profit_amount'  => 'decimal:2',
    ];

    public function distribution()
    {
        return $this->belongsTo(ProfitDistribution::class, 'profit_distribution_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id')->withDefault();
    }

    public function account()
    {
        return $this->belongsTo(SavingsAccount::class, 'account_id')->withoutGlobalScopes()->withDefault();
    }
}
