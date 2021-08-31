<?php

namespace Common\Workspaces\Policies;

use Common\Auth\BaseUser;
use Common\Core\Policies\BasePolicy;
use Common\Workspaces\Workspace;

class WorkspacePolicy extends BasePolicy
{
    public function index(BaseUser $user, int $userId = null)
    {
        return $user->hasPermission('workspaces.view') || $user->id === $userId;
    }

    public function show(BaseUser $user, Workspace $workspace)
    {
        return $user->hasPermission('workspaces.view') || $workspace->owner_id === $user->id || $workspace->isMember($user);
    }

    public function store(BaseUser $user)
    {
        return $this->storeWithCountRestriction($user, Workspace::class);
    }

    public function update(BaseUser $user, Workspace $workspace)
    {
        return $user->hasPermission('workspaces.update') || $workspace->owner_id === $user->id;
    }

    public function destroy(BaseUser $user, $workspaceIds)
    {
        if ($user->hasPermission('workspaces.delete')) {
            return true;
        } else {
            $dbCount = app(Workspace::class)
                ->whereIn('id', $workspaceIds)
                ->where('owner_id', $user->id)
                ->count();
            return $dbCount === count($workspaceIds);
        }
    }
}
