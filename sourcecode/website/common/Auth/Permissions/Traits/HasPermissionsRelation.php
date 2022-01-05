<?php

namespace Common\Auth\Permissions\Traits;


use Common\Auth\Permissions\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasPermissionsRelation
{
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(Permission::class, 'permissionable')
            ->withPivot('restrictions')
            ->select('name', 'permissions.id', 'permissions.restrictions');
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasPermission($name)
    {
        return !is_null($this->getPermission($name)) || !is_null($this->getPermission('admin'));
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasExactPermission($name)
    {
        return !is_null($this->getPermission($name));
    }

    /**
     * @param string $name
     * @return Permission
     */
    public function getPermission($name)
    {
        if (method_exists($this, 'loadPermissions')) {
            $this->loadPermissions();
        }

        foreach ($this->permissions as $permission) {
            if ($permission->name === $name) {
                return $permission;
            }
        }
    }
}
