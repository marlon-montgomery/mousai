<?php

namespace Common\Workspaces\Notifications;

use Common\Workspaces\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    const NOTIF_ID = 'W01';

    /**
     * @var string
     */
    private $workspace;
    /**
     * @var string
     */
    private $inviterName;
    /**
     * @var string
     */
    private $joinCode;

    public function __construct(Workspace $workspace, string $inviterName, string $joinCode)
    {
        $this->workspace = $workspace;
        $this->inviterName = $inviterName;
        $this->joinCode = $joinCode;
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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $data = [
            'inviter' => ucfirst($this->inviterName),
            'workspace' => ucfirst($this->workspace->name),
            'siteName' => config('app.name')
        ];

        return (new MailMessage)
            ->subject(__(':inviter invited you to :siteName :workspace', $data))
            ->line(__("Join your :workspace teammates on :siteName", $data))
            ->action(__('Join your team'), url("secure/workspace/join/{$this->joinCode}"))
            ->line(__('This invitation link will expire in 3 days.'))
            ->line(__('If you do not wish to join this workspace, no further action is required.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $translateData = [
            'inviter' => ucfirst($this->inviterName),
            'workspace' => ucfirst($this->workspace->name),
        ];

        return [
            'image' => 'group-add',
            'inviteId' => $this->joinCode,
            'lines' => [
                [
                    'content' => __(':inviter invited you to join :workspace.', $translateData),
                ],
                [
                    'content' => __('Accepting the invitation will give you access to links, domains, overlays and other resources in this workspace.'),
                ],
            ],
            'buttonActions' => [
                ['label' => 'Join', 'action' => 'join', 'color' => 'accent'],
                ['label' => 'Decline', 'action' => 'decline', 'color' => 'warn'],
            ]
        ];
    }
}
