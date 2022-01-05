<?php

namespace App\Notifications;

use App\Services\UrlGenerator;
use App\Track;
use App\User;
use Common\Comments\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Str;

class CommentReceivedReply extends Notification
{
    use Queueable;

    /**
     * @var array
     */
    public $newComment;

    /**
     * @var array
     */
    private $originalComment;

    /**
     * @var array
     */
    private $track;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * @param Comment $newComment
     * @param array $originalComment
     */
    public function __construct($newComment, $originalComment)
    {
        $this->newComment = $newComment;
        $this->originalComment = $originalComment;
        $track = app(Track::class)
            ->select(['name', 'id'])
            ->find($newComment['commentable_id']);
        $this->track = ['name' => $track->name, 'id' => $track->id];
        $this->urlGenerator = app(UrlGenerator::class);
    }

    /**
     * @param User $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * @param User $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $username = $this->newComment['user']['display_name'];

        return [
            'image' => $this->originalComment['user']['avatar'],
            'mainAction' => [
                'action' => $this->urlGenerator->track($this->track),
            ],
            'lines' => [
                [
                    'content' => __(':username replied to your comment:', ['username' => $username]),
                    'action' => ['action' => $this->urlGenerator->user($this->newComment['user']), 'label' => __('View user')],
                    'type' => 'secondary'
                ],
                [
                    'content' => '"'.Str::limit($this->newComment['content'], 180).'"',
                    'icon' => 'comment', 'type' => 'primary'
                ],
                [
                    'content' => __('on') . " {$this->track['name']}",
                    'type' => 'secondary'
                ],
            ],
        ];
    }
}
