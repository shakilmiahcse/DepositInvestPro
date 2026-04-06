<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMonthlyDepositReminderTemplate extends Migration {
    public function up() {
        $exists = DB::table('email_sms_templates')
            ->where('slug', 'MONTHLY_DEPOSIT_REMINDER')
            ->exists();

        if (!$exists) {
            DB::table('email_sms_templates')->insert([
                'name'                => 'Monthly Deposit Reminder',
                'slug'                => 'MONTHLY_DEPOSIT_REMINDER',
                'subject'             => 'Monthly Deposit Reminder',
                'email_body'          => "<p>Dear <strong>{{name}}</strong>,</p><p>This is a friendly reminder that your monthly deposit of <strong>{{amount}}</strong> for account <strong>{{account_number}}</strong> is due for <strong>{{dueMonth}}</strong>.</p><p>Please complete the deposit on time to keep your account in good standing.</p><p><strong>Deposit Details:</strong></p><ul><li><strong>Account Number:</strong> {{account_number}}</li><li><strong>Monthly Deposit Amount:</strong> {{amount}}</li><li><strong>Due Month:</strong> {{dueMonth}}</li><li><strong>Current Balance:</strong> {{balance}}</li></ul>",
                'sms_body'            => 'Dear {{name}}, your monthly deposit of {{amount}} for account {{account_number}} is due for {{dueMonth}}. Please deposit on time.',
                'notification_body'   => 'Dear {{name}}, your monthly deposit of {{amount}} for account {{account_number}} is due for {{dueMonth}}. Please deposit on time.',
                'shortcode'           => '{{name}} {{account_number}} {{amount}} {{dueMonth}} {{balance}} {{dateTime}}',
                'email_status'        => 0,
                'sms_status'          => 0,
                'notification_status' => 0,
                'template_mode'       => 0,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }

    public function down() {
        DB::table('email_sms_templates')
            ->where('slug', 'MONTHLY_DEPOSIT_REMINDER')
            ->delete();
    }
}
