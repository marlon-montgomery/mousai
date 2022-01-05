<?php

namespace App\Notifications;

use App\BackstageRequest;
use App\Services\UrlGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackstageRequestWasHandled extends Notification
{
    use Queueable;

    /**
     * @var BackstageRequest
     */
    private $backstageRequest;

    /**
     * @var string|null
     */
    private $notes;

    public function __construct(BackstageRequest $backstageRequest, ?string $notes)
    {
        $this->backstageRequest = $backstageRequest;
        $this->notes = $notes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->greeting(__('Hi :name,', ['name' => $this->backstageRequest->user->display_name]))
            ->subject(__(":sitename backstage request :status", [
                'sitename' => config('app.name'),
                'status' => $this->backstageRequest->status
            ]))
            ->line(__('Your backstage request was :status.', ['status' => $this->backstageRequest->status]));

        if ($this->notes) {
            $message->line($this->notes);
        }

        return $message->action(__('Open artist profile'), $this->getMainAction());
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'image' => 'task',
            'mainAction' => [
                'action' => $this->getMainAction(),
                'label' => __('Open artist profile'),
            ],
            'lines' => [
                [
                    'content' => __('Your backstage request was :status.', ['status' => $this->backstageRequest->status]),
                    'type' => 'primary'
                ],
            ],
        ];
    }

    private function getMainAction(): string
    {
        return $this->backstageRequest->artist ?
            app(UrlGenerator::class)->artist($this->backstageRequest->artist) :
            app(UrlGenerator::class)->home();
    }
}
