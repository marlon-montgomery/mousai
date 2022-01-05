<?php

namespace Common\Core\Policies;

use App\User;
use Common\Auth\BaseUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'tags.view');
    }

    public function show(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'tags.view');
    }

    public function store(BaseUser $user)
    {
        return $user->hasPermission('tags.create');
    }

    public function update(BaseUser $user)
    {
        return $user->hasPermission('tags.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('tags.delete');
    }
}
