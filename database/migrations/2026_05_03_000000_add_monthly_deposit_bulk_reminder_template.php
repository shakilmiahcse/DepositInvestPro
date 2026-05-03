<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMonthlyDepositBulkReminderTemplate extends Migration {
    public function up() {
        $exists = DB::table('email_sms_templates')
            ->where('slug', 'MONTHLY_DEPOSIT_BULK_REMINDER')
            ->exists();

        if (! $exists) {
            DB::table('email_sms_templates')->insert([
                'name'                => 'Monthly Deposit Bulk Reminder',
                'slug'                => 'MONTHLY_DEPOSIT_BULK_REMINDER',
                'subject'             => 'Monthly Deposit Reminder',
                'email_body'          => '<p>Dear Member,</p><p>This is a friendly reminder that your monthly deposit is still pending. Please complete the deposit on time.</p><p>Regards,<br>{{company_name}}</p>',
                'sms_body'            => null,
                'notification_body'   => null,
                'shortcode'           => '{{member_count}} {{deposit_count}} {{dateTime}} {{company_name}}',
                'email_status'        => 1,
                'sms_status'          => 0,
                'notification_status' => 0,
                'template_mode'       => 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }

    public function down() {
        DB::table('email_sms_templates')
            ->where('slug', 'MONTHLY_DEPOSIT_BULK_REMINDER')
            ->delete();
    }
}
