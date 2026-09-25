<?php

namespace App\Services;

use App\Models\EmailSMSTemplate;
use App\Models\MonthlyDeposit;
use App\Notifications\MonthlyDepositReminder;
use App\Utilities\Overrider;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class MonthlyDepositReminderService {
    public function getSettings(): array {
        return [
            'auto_enabled'        => get_option('monthly_deposit_auto_reminder_enabled', '0'),
            'time'                => get_option('monthly_deposit_reminder_time', '09:00') ?: '09:00',
            'mode'                => get_option('monthly_deposit_reminder_mode', 'all_except') ?: 'all_except',
            'member_ids'          => $this->getArraySetting('monthly_deposit_reminder_member_ids'),
            'excluded_member_ids' => $this->getArraySetting('monthly_deposit_reminder_excluded_member_ids'),
        ];
    }

    public function saveSettings(array $data): void {
        update_option('monthly_deposit_auto_reminder_enabled', $data['auto_enabled']);
        update_option('monthly_deposit_reminder_time', $data['time']);
        update_option('monthly_deposit_reminder_mode', $data['mode']);
        update_option('monthly_deposit_reminder_member_ids', json_encode(array_values($data['member_ids'])));
        update_option('monthly_deposit_reminder_excluded_member_ids', json_encode(array_values($data['excluded_member_ids'])));
    }

    public function sendBulkReminder(array $filters = []): array {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        Overrider::load('Settings');

        $template = EmailSMSTemplate::where('slug', 'MONTHLY_DEPOSIT_BULK_REMINDER')->first()
            ?: EmailSMSTemplate::where('slug', 'MONTHLY_DEPOSIT_REMINDER')->first();

        if ($template != null && (int) $template->email_status !== 1 && (int) $template->sms_status !== 1 && (int) $template->notification_status !== 1) {
            return $this->error(_lang('Monthly deposit bulk reminder is disabled. Please enable Email, SMS or Local Notification in Notification Templates.'));
        }

        $query = MonthlyDeposit::pending()
            ->with(['member', 'account.savings_type.currency'])
            ->whereHas('member');

        if (! empty($filters['month']) && is_numeric($filters['month'])) {
            $query->where('month', (int) $filters['month']);
        }

        if (! empty($filters['year']) && is_numeric($filters['year'])) {
            $query->where('year', (int) $filters['year']);
        }

        $this->applyRecipientSettings($query);

        $deposits = $query->get();

        if ($deposits->isEmpty()) {
            return $this->error(_lang('No pending monthly deposits found for reminder'));
        }

        $sentCount       = 0;
        $failedCount     = 0;
        $notifiedMembers = [];

        foreach ($deposits as $deposit) {
            if (! $deposit->member || ! $deposit->member->id || ! $deposit->account) {
                continue;
            }

            try {
                $deposit->member->notify(new MonthlyDepositReminder($deposit, 'MONTHLY_DEPOSIT_BULK_REMINDER'));
                $sentCount++;
                $notifiedMembers[$deposit->member_id] = true;
            } catch (Throwable $e) {
                $failedCount++;
            }
        }

        $memberCount = count($notifiedMembers);

        if ($sentCount === 0 && $failedCount > 0) {
            return $this->error(_lang('Failed to send reminders. Please check your mail/SMS settings.'));
        }

        return [
            'success'       => true,
            'message'       => _lang('Bulk reminder sent successfully') . ': ' . $sentCount . ' ' . _lang('reminder(s) sent to') . ' ' . $memberCount . ' ' . _lang('member(s)'),
            'member_count'  => $memberCount,
            'deposit_count' => $sentCount,
            'email_count'   => $sentCount,
        ];
    }

    public function sendScheduledReminder(): array {
        if (get_option('monthly_deposit_auto_reminder_enabled', '0') !== '1') {
            return $this->error(_lang('Monthly deposit auto reminder is disabled'));
        }

        $today = now()->toDateString();

        if (get_option('monthly_deposit_reminder_last_sent_date') === $today) {
            return $this->error(_lang('Monthly deposit reminder already sent today'));
        }

        $result = $this->sendBulkReminder();

        if ($result['success'] === true) {
            update_option('monthly_deposit_reminder_last_sent_date', $today);
        }

        return $result;
    }

    private function applyRecipientSettings(Builder $query): void {
        $settings = $this->getSettings();

        if ($settings['mode'] === 'selected_only') {
            if (empty($settings['member_ids'])) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->whereIn('member_id', $settings['member_ids']);
            return;
        }

        if (! empty($settings['excluded_member_ids'])) {
            $query->whereNotIn('member_id', $settings['excluded_member_ids']);
        }
    }

    private function getArraySetting(string $name): array {
        $value = get_option($name, '[]');

        if (is_array($value)) {
            return $this->sanitizeIds($value);
        }

        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return $this->sanitizeIds($decoded);
        }

        $unserialized = @unserialize((string) $value);

        if (is_array($unserialized)) {
            return $this->sanitizeIds($unserialized);
        }

        if (trim((string) $value) === '') {
            return [];
        }

        return $this->sanitizeIds(explode(',', (string) $value));
    }

    private function sanitizeIds(array $ids): array {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function error(string $message): array {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}
