<?php

namespace App\Console\Commands;

use App\Models\MonthlyDeposit;
use App\Models\SavingsAccount;
use Illuminate\Console\Command;

class GenerateMonthlyDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthly:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly deposit records for active accounts on the 1st day of each month.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // if (now()->day !== 1) {
        //     $this->info('This command only runs on the 1st day of the month.');
        //     return 0;
        // }

        $month = now()->month;
        $year  = now()->year;

        $accounts = SavingsAccount::with('savings_type')->get();
        $created  = 0;

        foreach ($accounts as $account) {
            $amount = $account->monthly_deposit_amount ?: optional($account->savings_type)->monthly_deposit_amount;
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

        $this->info("Monthly deposit records generated: {$created}");

        return 0;
    }
}
