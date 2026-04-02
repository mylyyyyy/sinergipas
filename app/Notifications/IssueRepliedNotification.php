<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueRepliedNotification extends Notification
{
    use Queueable;

    protected $issue;

    public function __construct($issue)
    {
        $this->issue = $issue;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'issue_id' => $this->issue->id,
            'subject' => $this->issue->subject,
            'message' => 'Admin telah membalas laporan Anda: ' . $this->issue->subject,
            'admin_note' => $this->issue->admin_note,
            'link' => route('profile.index'),
        ];
    }
}
