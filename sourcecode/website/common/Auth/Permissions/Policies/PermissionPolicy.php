<?php

namespace Common\Auth\Permissions\Policies;

use Common\Auth\Permissions\Permission;
use Common\Auth\BaseUser;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(BaseUser $user)
    {
        return $user->hasPermission('permission.view');
    }

    public function show(BaseUser $user, Permission $permission)
    {
        return $user->hasPermission('permission.view');
    }

    public function store(BaseUser $user)
    {
        return $user->hasPermission('permission.create');
    }

    public function update(BaseUser $user)
    {
        return $user->hasPermission('permission.update');
    }

    public function destroy(BaseUser $user)
    {
        return $user->hasPermission('permission.delete');
    }
}
