<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->call(new \App\Cronjobs\YearlyMaintenanceFeePosting)->hourly();
        $schedule->command('monthly:generate')->monthlyOn(1, '00:00');
        $schedule->call(new \App\Cronjobs\OverdueLoanNotification)->everyThirtyMinutes();
        $schedule->call(new \App\Cronjobs\UpcommingLoanNotification)->everyTenMinutes();

        $monthlyDepositReminderTime = get_option('monthly_deposit_reminder_time', '09:00') ?: '09:00';
        if (! preg_match('/^\d{2}:\d{2}$/', $monthlyDepositReminderTime)) {
            $monthlyDepositReminderTime = '09:00';
        }

        $schedule->call(new \App\Cronjobs\MonthlyDepositReminderNotification)
            ->dailyAt($monthlyDepositReminderTime)
            ->withoutOverlapping();
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        $timeZone = get_option('timezone', 'Asia/Dhaka');
        config(['app.timezone' =>  $timeZone ]);
        return $timeZone;
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
