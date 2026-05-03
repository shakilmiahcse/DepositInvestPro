<?php

namespace App\Services;

use App\Mail\GeneralMail;
use App\Models\EmailSMSTemplate;
use App\Models\MonthlyDeposit;
use App\Utilities\Overrider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MonthlyDepositReminderService {
    private const BULK_TEMPLATE_SLUG = 'MONTHLY_DEPOSIT_BULK_REMINDER';

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
        Overrider::load('Settings');

        $template = EmailSMSTemplate::where('slug', self::BULK_TEMPLATE_SLUG)->first();

        if ($template != null && (int) $template->email_status !== 1) {
            return $this->error(_lang('Bulk reminder email template is disabled'));
        }

        $query = MonthlyDeposit::pending()
            ->with('member')
            ->whereHas('member', function (Builder $query) {
                $query->whereNotNull('email')
                    ->where('email', '!=', '');
            });

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

        $members = $deposits->pluck('member')
            ->filter(fn ($member) => $member != null && $member->id != null)
            ->unique('id')
            ->values();

        $emails = $members->pluck('email')
            ->map(fn ($email) => trim(strtolower((string) $email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if (empty($emails)) {
            return $this->error(_lang('No valid email recipients found for reminder'));
        }

        $toAddress = $this->getToAddress();

        if (! filter_var($toAddress, FILTER_VALIDATE_EMAIL)) {
            return $this->error(_lang('Please configure a valid From Email before sending reminders'));
        }

        $mail          = new \stdClass();
        $mail->subject = $template != null ? $template->subject : _lang('Monthly Deposit Reminder');
        $mail->body    = processShortCode($template != null ? $template->email_body : $this->defaultBulkEmailBody(), [
            'member_count'  => (string) $members->count(),
            'deposit_count' => (string) $deposits->count(),
            'dateTime'      => now()->format(get_date_format() . ' ' . get_time_format()),
            'company_name'  => get_option('company_name', get_option('site_title', config('app.name'))),
        ]);

        try {
            foreach (array_chunk($emails, 50) as $emailChunk) {
                Mail::to($toAddress)->bcc($emailChunk)->send(new GeneralMail($mail));
            }
        } catch (Throwable $e) {
            return $this->error(_lang('Failed to send reminder') . ': ' . $e->getMessage());
        }

        return [
            'success'       => true,
            'message'       => _lang('Bulk reminder sent successfully') . ': ' . count($emails),
            'member_count'  => $members->count(),
            'deposit_count' => $deposits->count(),
            'email_count'   => count($emails),
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

    private function getToAddress(): string {
        return get_option('from_email')
            ?: get_option('email')
            ?: config('mail.from.address', '');
    }

    private function defaultBulkEmailBody(): string {
        return '<p>Dear Member,</p><p>This is a friendly reminder that your monthly deposit is still pending. Please complete the deposit on time.</p><p>Regards,<br>{{company_name}}</p>';
    }

    private function error(string $message): array {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}
