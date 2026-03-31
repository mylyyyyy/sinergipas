<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewDocumentNotification extends Notification
{
    use Queueable;

    protected $document;

    public function __construct($document)
    {
        $this->document = $document;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Dokumen Baru Tersedia',
            'message' => 'Dokumen "' . $this->document->title . '" telah diunggah ke akun Anda.',
            'document_id' => $this->document->id,
            'category' => $this->document->category->name,
        ];
    }
}
