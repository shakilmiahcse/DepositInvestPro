<?php

namespace App\Notifications;

use App\Channels\SmsMessage;
use App\Models\EmailSMSTemplate;
use App\Utilities\Overrider;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyDepositReminder extends Notification {
    use Queueable;

    private $monthlyDeposit;
    private $template;
    private $replace = [];

    public function __construct($monthlyDeposit, string $templateSlug = 'MONTHLY_DEPOSIT_REMINDER') {
        Overrider::load("Settings");

        $this->monthlyDeposit = $monthlyDeposit;
        $this->template       = EmailSMSTemplate::where('slug', $templateSlug)->first()
            ?: EmailSMSTemplate::where('slug', 'MONTHLY_DEPOSIT_REMINDER')->first();

        $currency = ($this->monthlyDeposit->account && $this->monthlyDeposit->account->savings_type && $this->monthlyDeposit->account->savings_type->currency) 
            ? $this->monthlyDeposit->account->savings_type->currency->name 
            : '';
        $balance  = get_account_balance($this->monthlyDeposit->account_id, $this->monthlyDeposit->member_id);
        $dueMonth = date('F Y', mktime(0, 0, 0, $this->monthlyDeposit->month, 1, $this->monthlyDeposit->year));

        $this->replace['name']           = $this->monthlyDeposit->member ? $this->monthlyDeposit->member->name : '';
        $this->replace['account_number'] = $this->monthlyDeposit->account ? $this->monthlyDeposit->account->account_number : '';
        $this->replace['amount']         = decimalPlace($this->monthlyDeposit->amount, currency($currency));
        $this->replace['balance']        = decimalPlace($balance, currency($currency));
        $this->replace['dueMonth']       = $dueMonth;
        $this->replace['dateTime']       = now()->format(get_date_format() . ' ' . get_time_format());
    }

    public function via($notifiable) {
        $channels = [];

        if ($this->template != null && $this->template->email_status == 1) {
            array_push($channels, 'mail');
        }
        if ($this->template != null && $this->template->sms_status == 1) {
            array_push($channels, \App\Channels\SMS::class);
        }
        if ($this->template != null && $this->template->notification_status == 1) {
            array_push($channels, 'database');
        }

        return $channels;
    }

    public function toMail($notifiable) {
        $subject = ($this->template && $this->template->subject) ? $this->template->subject : _lang('Monthly Deposit Reminder');
        $body    = ($this->template && $this->template->email_body) ? $this->template->email_body : '<p>Dear <strong>{{name}}</strong>,</p><p>This is a friendly reminder that your monthly deposit of <strong>{{amount}}</strong> for account <strong>{{account_number}}</strong> is due for <strong>{{dueMonth}}</strong>.</p>';
        $message = processShortCode($body, $this->replace);

        return (new MailMessage)
            ->subject($subject)
            ->markdown('email.notification', ['message' => $message]);
    }

    public function toSMS($notifiable) {
        if (! $this->template || ! $this->template->sms_body) {
            return null;
        }

        $message = processShortCode($this->template->sms_body, $this->replace);

        return (new SmsMessage())
            ->setContent($message)
            ->setRecipient($notifiable->country_code . $notifiable->mobile);
    }

    public function toArray($notifiable) {
        if (! $this->template || ! $this->template->notification_body) {
            return ['message' => ''];
        }

        $message = processShortCode($this->template->notification_body, $this->replace);

        return ['message' => $message];
    }
}
