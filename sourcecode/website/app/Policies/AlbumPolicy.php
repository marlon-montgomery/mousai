<?php

namespace App\Policies;

use App\Album;
use App\Artist;
use App\User;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Builder;

class AlbumPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function index(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'albums.view') || $this->userOrGuestHasPermission($user, 'music.view');
    }

    public function show(?User $user, Album $album)
    {
        if ($album->owner_id === $user->id) {
            return true;
        } else if ($this->userOrGuestHasPermission($user, 'albums.view') || $this->userOrGuestHasPermission($user, 'music.view')) {
            return true;
        } else if ($user) {
            $managedArtists = $user->artists()->pluck('artists.id');
            $albumArtists = $album->artists->pluck('pivot.artist_id');
            return $albumArtists->intersect($managedArtists)->isNotEmpty();
        }
        return false;
    }

    public function store(User $user)
    {
        return $user->hasPermission('albums.create') || $user->hasPermission('music.create');
    }

    public function update(User $user, Album $album)
    {
        if ($album->owner_id === $user->id) {
            return true;
        } else if ($this->userOrGuestHasPermission($user, 'albums.update') || $this->userOrGuestHasPermission($user, 'music.update')) {
            return true;
        } else if ($user) {
            $managedArtists = $user->artists()->pluck('artists.id');
            $albumArtists = $album->artists->pluck('pivot.artist_id');
            return $albumArtists->intersect($managedArtists)->isNotEmpty();
        }
        return false;
    }

    public function destroy(User $user, $albumIds)
    {
        if ($user->hasPermission('albums.delete') || $user->hasPermission('music.delete')) {
            return true;
        } else {
            $managedArtists = $user->artists()->pluck('artists.id');
            $dbCount = Album::whereIn('albums.id', $albumIds)
                ->where(function(Builder $builder) use($user, $managedArtists, $albumIds) {
                    $builder->where('owner_id', $user->id)
                        ->orWhereHas('artists', function(Builder $builder) use($managedArtists) {
                            $builder->whereIn('artists.id', $managedArtists);
                        }, '=', count($albumIds));
                })
                ->count();
            return $dbCount === count($albumIds);
        }
    }
}
