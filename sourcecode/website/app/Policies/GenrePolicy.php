<?php

namespace App\Policies;

use App\User;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class GenrePolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'genres.view') || $this->userOrGuestHasPermission($user, 'music.view');
    }

    public function show(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'genres.view') || $this->userOrGuestHasPermission($user, 'music.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('genres.create') || $user->hasPermission('music.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('genres.update') || $user->hasPermission('music.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('genres.delete') || $user->hasPermission('music.delete');
    }
}
