<?php

namespace App\Policies;

use App\Album;
use App\Track;
use Common\Auth\BaseUser;
use Common\Comments\Comment;
use Common\Comments\CommentPolicy;
use Illuminate\Database\Eloquent\Builder;

class TrackCommentPolicy extends CommentPolicy
{
    public function destroy(BaseUser $user, $commentIds)
    {
        if (
            $user->hasPermission("comments.delete") ||
            $user->hasPermission("music.delete")
        ) {
            return true;
        } else {
            $managedArtists = $user->artists()->pluck("artists.id");
            $comments = Comment::with("commentable.artists")
                ->where(function (Builder $builder) {
                    $builder
                        ->where("commentable_type", Track::class)
                        ->orWhere("commentable_type", Album::class);
                })
                ->whereIn("comments.id", $commentIds)
                ->get();

            $dbCount = $comments
                ->filter(function (Comment $comment) use (
                    $user,
                    $managedArtists
                ) {
                    return $comment->user_id === $user->id ||
                        $comment->commentable->artists->contains(
                            $managedArtists,
                        );
                })
                ->count();

            return $dbCount === count($commentIds);
        }
    }
}
