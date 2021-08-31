<?php

namespace App\Policies;

use App\User;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class LyricPolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'lyrics.view');
    }

    public function show(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'lyrics.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('lyrics.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('lyrics.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('lyrics.delete');
    }
}
