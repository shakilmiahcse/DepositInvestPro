<?php

namespace App\Cronjobs;

use App\Services\MonthlyDepositReminderService;
use Throwable;

class MonthlyDepositReminderNotification {
    public function __invoke() {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        try {
            app(MonthlyDepositReminderService::class)->sendScheduledReminder();
        } catch (Throwable $e) {}
    }
}
