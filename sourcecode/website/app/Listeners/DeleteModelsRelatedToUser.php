<?php

namespace App\Listeners;

use App\Actions\Track\DeleteTracks;
use App\ProfileLink;
use App\Services\Albums\DeleteAlbums;
use App\Services\Playlists\DeletePlaylists;
use App\TrackPlay;
use App\User;
use App\UserProfile;
use Common\Auth\Events\UsersDeleted;
use Common\Comments\Comment;
use DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeleteModelsRelatedToUser
{
    public function handle(UsersDeleted $event)
    {
        $userIds = $event->users->pluck('id');

        // delete albums
        $albumIds = $event->users
            ->load('uploadedAlbums')
            ->pluck('uploadedAlbums.*.id')
            ->flatten(1);
        app(DeleteAlbums::class)->execute($albumIds);

        // delete tracks
        $trackIds = $event->users
            ->load('uploadedTracks')
            ->pluck('uploadedTracks.*.id')
            ->flatten(1);
        app(DeleteTracks::class)->execute($trackIds->toArray());

        // detach user from comments
        Comment::whereIn('user_id', $userIds)->update(['user_id' => 0]);

        // clean up follows table
        DB::table('follows')
            ->whereIn('follower_id', $userIds)
            ->orWhereIn('followed_id', $userIds)
            ->delete();

        // likes
        DB::table('likes')
            ->whereIn('user_id', $userIds)
            ->delete();

        // playlists
        $playlists = $event->users
            ->load([
                'playlists' => function (BelongsToMany $builder) use ($event) {
                    $builder->whereIn('owner_id', $event->users->pluck('id'));
                },
            ])
            ->pluck('playlists')
            ->flatten(1);
        app(DeletePlaylists::class)->execute($playlists);
        DB::table('playlist_user')
            ->whereIn('user_id', $userIds)
            ->delete();

        // reposts
        DB::table('reposts')
            ->whereIn('user_id', $userIds)
            ->delete();

        // detach user from track plays
        TrackPlay::whereIn('user_id', $userIds)->update(['user_id' => 0]);

        // profiles
        UserProfile::whereIn('user_id', $userIds)->delete();
        ProfileLink::whereIn('linkeable_id', $userIds)
            ->where('linkeable_type', User::class)
            ->delete();
    }
}
