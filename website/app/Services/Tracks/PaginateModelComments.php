<?php

namespace App\Services\Tracks;

use App\Album;
use App\Track;
use Common\Comments\Comment;
use Common\Comments\LoadChildComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaginateModelComments
{
    /**
     * @param Album|Track $track
     * @return array
     */
    public function execute(Model $track): array
    {
        $pagination = $track->comments()
            ->rootOnly()
            ->with(['user' => function(BelongsTo $builder) {
                $builder->compact();
            }])
            ->paginate(request('perPage') ?? 25);

        $comments = app(LoadChildComments::class)
            ->execute($track, collect($pagination->items()));

        $comments->transform(function(Comment $comment) {
            $comment->relative_created_at = $comment->created_at->diffForHumans();
            unset($comment->created_at);
            unset($comment->updated_at);
            if ($comment->deleted) {
                $comment->content = null;
                $comment->setRelation('user', null);
            }
            return $comment;
        });

        $pagination = $pagination->toArray();
        $pagination['data'] = $comments;

        return $pagination;
    }
}
