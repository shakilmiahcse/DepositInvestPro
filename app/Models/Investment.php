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
}
