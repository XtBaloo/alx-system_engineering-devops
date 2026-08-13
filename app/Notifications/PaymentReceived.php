<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\SchoolSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $currency = SchoolSetting::current()->currency_symbol;

        return [
            'type' => 'payment-received',
            'title' => 'Payment received',
            'message' => "A payment of {$currency}".number_format((float) $this->payment->amount, 2)." was recorded (Receipt {$this->payment->receipt_number}).",
            'url' => route('my.fees'),
        ];
    }
}
