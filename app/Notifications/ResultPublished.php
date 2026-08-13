<?php

namespace App\Notifications;

use App\Models\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResultPublished extends Notification
{
    use Queueable;

    public function __construct(public Result $result)
    {
        $this->result->loadMissing('subject', 'term');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'result-published',
            'title' => 'Result published',
            'message' => "{$this->result->subject->name} result for {$this->result->term->name} has been published.",
            'url' => route('my.results'),
        ];
    }
}
