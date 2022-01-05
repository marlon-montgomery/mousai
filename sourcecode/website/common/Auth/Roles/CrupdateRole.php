<?php

namespace Common\Auth\Roles;

use Common\Auth\Permissions\Traits\SyncsPermissions;
use Arr;

class CrupdateRole
{
    use SyncsPermissions;

    /**
     * @var Role
     */
    private $role;

    /**
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    /**
     * @param array $data
     * @param Role $role
     * @return Role
     */
    public function execute($data, $role = null)
    {
        if ( ! $role) {
            $role = $this->role->newInstance([]);
        }

        $attributes = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'default' => $data['default'] ?? false,
            'guests' => $data['guests'] ?? false,
            'type' => $data['type'] ?? 'sitewide',
        ];

        $role->fill($attributes)->save();

        // always sync permissions, detach all if "null" is given as permissions
        $this->syncPermissions($role, Arr::get($data, 'permissions', []));

        return $role;
    }
}
