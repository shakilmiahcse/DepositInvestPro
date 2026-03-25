<?php

namespace App\Console\Commands;

use App\Services\MonthlyDepositService;
use Illuminate\Console\Command;

class GenerateMonthlyDeposits extends Command
{
    protected MonthlyDepositService $monthlyDepositService;

    public function __construct(MonthlyDepositService $monthlyDepositService)
    {
        parent::__construct();

        $this->monthlyDepositService = $monthlyDepositService;
    }

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
        $created = $this->monthlyDepositService->generateForMonth(now());

        $this->info("Monthly deposit records generated: {$created}");

        return 0;
    }
}
