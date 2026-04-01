<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentUploadedNotification extends Notification
{
    use Queueable;

    protected $document;
    protected $employee;

    public function __construct($document, $employee)
    {
        $this->document = $document;
        $this->employee = $employee;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Unggahan Baru: ' . $this->employee->full_name)
            ->greeting('Halo Admin,')
            ->line('Pegawai ' . $this->employee->full_name . ' telah mengunggah dokumen baru.')
            ->line('Judul Dokumen: ' . $this->document->title)
            ->line('Kategori: ' . $this->document->category->name)
            ->action('Lihat Pusat Dokumen', url('/documents'))
            ->line('Harap segera diperiksa jika diperlukan.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Pegawai Unggah Dokumen',
            'message' => $this->employee->full_name . ' mengunggah "' . $this->document->title . '"',
            'document_id' => $this->document->id,
            'employee_name' => $this->employee->full_name,
        ];
    }
}
