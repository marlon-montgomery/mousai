<?php

namespace Common\Domains;

use App\User;
use Common\Core\Policies\BasePolicy;

class CustomDomainPolicy extends BasePolicy
{
    public $permissionName = 'custom_domains';

    public function index(User $user, int $userId = null)
    {
        return $user->hasPermission("$this->permissionName.view") ||
            $user->id === $userId;
    }

    public function show(User $user, CustomDomain $customDomain)
    {
        return $user->hasPermission("$this->permissionName.view") ||
            $customDomain->user_id === $user->id;
    }

    public function store(User $user)
    {
        return $this->storeWithCountRestriction($user, CustomDomain::class);
    }

    public function update(User $user)
    {
        return $user->hasPermission("$this->permissionName.update");
    }

    public function destroy(User $user, array $domainIds)
    {
        if ($user->hasPermission("$this->permissionName.delete")) {
            return true;
        } else {
            $dbCount = app(CustomDomain::class)
                ->whereIn('id', $domainIds)
                ->where('user_id', $user->id)
                ->count();
            return $dbCount === count($domainIds);
        }
    }
}
