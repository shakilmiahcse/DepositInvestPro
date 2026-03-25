<?php

namespace App\Cronjobs;

use App\Services\MonthlyDepositService;

class MonthlyDepositPosting {
    protected MonthlyDepositService $monthlyDepositService;

    public function __construct()
    {
        $this->monthlyDepositService = app(MonthlyDepositService::class);
    }

    public function __invoke() {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        // Run only on the first day of the month
        if (date('j') !== '1') {
            return;
        }

        $this->monthlyDepositService->generateForMonth(now());
    }
}
