<?php

namespace App\Policies;

use App\Playlist;
use App\User;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Collection;

class PlaylistPolicy extends BasePolicy
{
    public function index(?User $user, $userId = null)
    {
        return $this->userOrGuestHasPermission($user, 'playlists.view') || ($user && $user->id === (int) $userId);
    }

    public function show(?User $user, Playlist $playlist)
    {
        return ($playlist->public && $this->userOrGuestHasPermission($user, 'playlists.view')) || $playlist->editors->contains('id', $user->id);
    }

    public function store(User $user)
    {
        return $user->hasPermission('playlists.create');
    }

    public function update(User $user, Playlist $playlist)
    {
        return $user->hasPermission('playlists.update') || $playlist->owner_id === $user->id || $playlist->editors->contains('id', $user->id);
    }

    public function modifyTracks(User $user, Playlist $playlist)
    {
        return $playlist->collaborative || $playlist->editors->contains('id', $user->id);
    }

    public function destroy(User $user, Collection $playlists)
    {
       if ($user->hasPermission('playlists.delete')) return true;

        return $playlists->filter(function(Playlist $playlist) use($user) {
            return ! $playlist->editors->contains('id', $user->id);
        })->count() === 0;
    }
}
