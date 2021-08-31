<?php

namespace Common\Comments;

use App\Notifications\CommentReceivedReply;
use App\User;
use Auth;
use Illuminate\Support\Arr;

class CrupdateComment
{
    /**
     * @var Comment
     */
    private $comment;

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param array $data
     * @param Comment $initialComment
     * @return Comment
     */
    public function execute($data, $initialComment = null)
    {
        if ( ! $initialComment) {
            $comment = $this->comment->newInstance([
                 'user_id' => Auth::id(),
            ]);
        } else {
            $comment = $initialComment;
        }

        $inReplyTo = Arr::get($data, 'inReplyTo');

        // specific app might need to store
        // some extra data along with comment
        $attributes = Arr::except($data, 'inReplyTo');
        if ($inReplyTo) {
            $attributes['parent_id'] = $inReplyTo['id'];
        }

        if (isset($attributes['commentable_type'])) {
            // track => App\Track
            $attributes['commentable_type'] = 'App\\' . ucfirst($data['commentable_type']);
        }
        $comment->fill($attributes)->save();

        $comment->generatePath();

        if ( ! $initialComment && $inReplyTo) {
            app(User::class)
                ->find($inReplyTo['user']['id'])
                ->notify(new CommentReceivedReply($comment, $inReplyTo));
        }

        return $comment;
    }
}
