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

    public function __construct($monthlyDeposit) {
        Overrider::load("Settings");

        $this->monthlyDeposit = $monthlyDeposit;
        $this->template       = EmailSMSTemplate::where('slug', 'MONTHLY_DEPOSIT_REMINDER')->first();

        $currency  = $this->monthlyDeposit->account->savings_type->currency->name;
        $balance   = get_account_balance($this->monthlyDeposit->account_id, $this->monthlyDeposit->member_id);
        $dueMonth  = date('F Y', mktime(0, 0, 0, $this->monthlyDeposit->month, 1, $this->monthlyDeposit->year));

        $this->replace['name']           = $this->monthlyDeposit->member->name;
        $this->replace['account_number'] = $this->monthlyDeposit->account->account_number;
        $this->replace['amount']         = decimalPlace($this->monthlyDeposit->amount, currency($currency));
        $this->replace['balance']        = decimalPlace($balance, currency($currency));
        $this->replace['dueMonth']       = $dueMonth;
        $this->replace['dateTime']       = now();
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
        $message = processShortCode($this->template->email_body, $this->replace);

        return (new MailMessage)
            ->subject($this->template->subject)
            ->markdown('email.notification', ['message' => $message]);
    }

    public function toSMS($notifiable) {
        $message = processShortCode($this->template->sms_body, $this->replace);

        return (new SmsMessage())
            ->setContent($message)
            ->setRecipient($notifiable->country_code . $notifiable->mobile);
    }

    public function toArray($notifiable) {
        $message = processShortCode($this->template->notification_body, $this->replace);

        return ['message' => $message];
    }
}
