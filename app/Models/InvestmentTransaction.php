<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentTransaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'investment_transactions';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class, 'investment_id');
    }
}
