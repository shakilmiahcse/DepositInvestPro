<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyDeposit extends Model
{
    protected $table = 'monthly_deposits';

    protected $guarded = ['id'];

    protected $dates = ['paid_date'];

    public function account()
    {
        return $this->belongsTo(SavingsAccount::class, 'account_id')->withDefault();
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id')->withDefault();
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id')->withDefault();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
