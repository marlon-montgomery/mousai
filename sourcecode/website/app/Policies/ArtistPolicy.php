<?php

namespace App\Policies;

use App\Artist;
use App\User;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArtistPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function index(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'artists.view') || $this->userOrGuestHasPermission($user, 'music.view');
    }

    public function show(?User $user, Artist $artist)
    {
        if ($this->userOrGuestHasPermission($user, 'artists.view') || $this->userOrGuestHasPermission($user, 'music.view')) {
            return true;
        } else if ($user) {
            $managedArtists = $user->artists()->pluck('artists.id');
            return $managedArtists->contains($artist->id);
        }
        return false;
    }

    public function store(User $user)
    {
        return $user->hasPermission('artists.create') || $user->hasPermission('music.create');
    }

    public function update(User $user, Artist $artist)
    {
        if ($this->userOrGuestHasPermission($user, 'artists.update') || $this->userOrGuestHasPermission($user, 'music.update')) {
            return true;
        } else if ($user) {
            $managedArtists = $user->artists()->pluck('artists.id');
            return $managedArtists->contains($artist->id);
        }
        return false;
    }

    public function destroy(User $user, array $artistIds)
    {
        if ($user->hasPermission('artists.delete') || $user->hasPermission('music.delete')) {
            return true;
        } else {
            $managedArtists = $user->artists()->pluck('artists.id');
            $dbArtists = Artist::whereIn('artists.id', $artistIds)->pluck('artists.id');
            return $dbArtists->intersect($managedArtists)->count() === count($artistIds);
        }
    }
}
