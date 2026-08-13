<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AnnouncementPosted extends Notification
{
    use Queueable;

    public function __construct(public Announcement $announcement) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'announcement',
            'title' => 'New announcement: '.$this->announcement->title,
            'message' => Str::limit(strip_tags($this->announcement->message), 120),
            'url' => route('announcements.index'),
        ];
    }
}
