<?php

namespace App\Cronjobs;

use App\Models\MonthlyDeposit;
use App\Models\SavingsAccount;

class MonthlyDepositPosting {

    public function __invoke() {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        // Run only on the first day of the month
        if (date('j') !== '1') {
            return;
        }

        $month = date('m');
        $year  = date('Y');

        $accounts = SavingsAccount::with('savings_type')->get();

        foreach ($accounts as $account) {
            $amount = $account->monthly_deposit_amount ?: optional($account->savings_type)->monthly_deposit_amount ?: 0;

            if ($amount <= 0) {
                continue;
            }

            MonthlyDeposit::firstOrCreate([
                'account_id' => $account->id,
                'month'      => $month,
                'year'       => $year,
            ], [
                'member_id' => $account->member_id,
                'amount'    => $amount,
                'status'    => 'pending',
            ]);
        }
    }
}
