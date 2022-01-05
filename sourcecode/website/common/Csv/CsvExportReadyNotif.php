<?php

namespace Common\Csv;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CsvExportReadyNotif extends Notification
{
    use Queueable;

    /**
     * @var CsvExport
     */
    protected $csvExport;

    /**
     * @var string
     */
    protected $exportName;

    public function __construct(CsvExport $csvExport, string $exportName)
    {
        $this->csvExport = $csvExport;
        $this->exportName = $exportName;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->line($this->primaryLine())
            ->line(__('This download link will only work if you are logged in as user who has requested the export and it will expire in one day.'))
            ->action('Download', $this->csvExport->downloadLink());
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            'image' => 'export-csv',
            'mainAction' => [
                'Label' => 'Download',
                'action' => $this->csvExport->downloadLink(),
            ],
            'lines' => [
                [
                    'content' => $this->primaryLine(),
                ],
                [
                    'content' => __('This download link will expire in one day.'),
                ],
            ],
        ];
    }

    protected function primaryLine(): string
    {
        return __(':name CSV export you have requested is ready to download.', ['name' => ucfirst($this->exportName)]);
    }
}
