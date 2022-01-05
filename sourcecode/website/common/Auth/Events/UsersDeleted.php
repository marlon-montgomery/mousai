<?php

namespace Common\Auth\Events;

use App\User;
use Illuminate\Database\Eloquent\Collection;

class UsersDeleted
{
    /**
     * @var User[]|Collection
     */
    public $users;

    /**
     * @param User[]|Collection
     */
    public function __construct($users)
    {
        $this->users = $users;
    }
}