<?php

namespace App\Http\Controllers\UserProfile;

use App\User;
use Common\Core\BaseController;

class UserFollowedUsersController extends BaseController
{
    public function index(User $user)
    {
        $this->authorize('show', $user);

        $pagination = $user
            ->followedUsers()
            ->withCount(['followers'])
            ->paginate(request('perPage') ?? 20);

        return $this->success(['pagination' => $pagination]);
    }
}
