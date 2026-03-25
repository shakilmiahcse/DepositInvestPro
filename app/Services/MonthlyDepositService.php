<?php

namespace App\Services;

use App\Models\MonthlyDeposit;
use App\Models\SavingsAccount;
use Carbon\Carbon;

class MonthlyDepositService
{
    public function generateForMonth(?Carbon $date = null): int
    {
        $date = $date ?: now();

        $month    = (int) $date->month;
        $year     = (int) $date->year;
        $accounts = SavingsAccount::with('savings_type')->get();
        $created  = 0;

        foreach ($accounts as $account) {
            $amount = (float) ($account->monthly_deposit_amount ?: optional($account->savings_type)->monthly_deposit_amount ?: 0);

            if ($amount <= 0) {
                continue;
            }

            $deposit = MonthlyDeposit::firstOrCreate([
                'account_id' => $account->id,
                'month'      => $month,
                'year'       => $year,
            ], [
                'member_id' => $account->member_id,
                'amount'    => $amount,
                'status'    => 'pending',
            ]);

            if ($deposit->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    public function hasMissingForMonth(?Carbon $date = null): bool
    {
        $date = $date ?: now();

        $month    = (int) $date->month;
        $year     = (int) $date->year;
        $accounts = SavingsAccount::with('savings_type')->get();

        foreach ($accounts as $account) {
            $amount = (float) ($account->monthly_deposit_amount ?: optional($account->savings_type)->monthly_deposit_amount ?: 0);

            if ($amount <= 0) {
                continue;
            }

            $exists = MonthlyDeposit::where('account_id', $account->id)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if (! $exists) {
                return true;
            }
        }

        return false;
    }
}
