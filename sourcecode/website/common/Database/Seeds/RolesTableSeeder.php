<?php namespace Common\Database\Seeds;

use Common\Auth\Permissions\Permission;
use Common\Auth\Permissions\Traits\SyncsPermissions;
use DB;
use Carbon\Carbon;
use App\User;
use Common\Auth\Roles\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RolesTableSeeder extends Seeder
{
    use SyncsPermissions;

    /**
     * @var Role
     */
    private $role;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var array
     */
    private $commonConfig;

    /**
     * @var array
     */
    private $appConfig;

    /**
     * @param Role $role
     * @param User $user
     * @param Permission $permission
     * @param Filesystem $fs
     */
    public function __construct(Role $role, User $user, Permission $permission, Filesystem $fs)
    {
        $this->user = $user;
        $this->role = $role;
        $this->permission = $permission;
        $this->fs = $fs;
    }

    /**
     * @return void
     */
    public function run()
    {
        $this->commonConfig = $this->fs->getRequire(app('path.common') . '/resources/defaults/permissions.php');
        $this->appConfig = $this->fs->getRequire(resource_path('defaults/permissions.php'));

        foreach ($this->appConfig['roles'] as $appRole) {
            if ($commonRoleName = Arr::get($appRole, 'extends')) {
                $commonRole = $this->findRoleConfig($commonRoleName);
                $appRole = array_merge($commonRole, $appRole);
                $appRole['permissions'] = array_merge($commonRole['permissions'], $appRole['permissions']);
            }

            // skip billing permissions if billing is not integrated
            $appRole['permissions'] = array_filter($appRole['permissions'], function($permission) {
                if (is_array($permission)) {
                    $permission = $permission['name'];
                }
                return config('common.site.billing_integrated') || ! Str::contains($permission, ['invoice.', 'plans.']);
            });

            $this->createOrUpdateRole($appRole);
        }

        $this->attachUsersRoleToExistingUsers();
    }

    /**
     * @param string $roleName
     * @return array
     */
    private function findRoleConfig($roleName)
    {
        $roleConfig = Arr::first($this->commonConfig['roles'], function($role) use($roleName) {
            return $role['name'] === $roleName;
        });
        if ( ! $roleConfig) {
            $roleConfig = Arr::first($this->appConfig['roles'], function($role) use($roleName) {
                return $role['name'] === $roleName;
            });
        }
        return $roleConfig;
    }

    /**
     * @param array $appRole
     * @return Role
     */
    private function createOrUpdateRole($appRole)
    {
        $defaultPermissions = collect($appRole['permissions'])->map(function($permission) {
            return is_string($permission) ? ['name' => $permission] : $permission;
        });

        $dbPermissions = $this->permission->whereIn('name', $defaultPermissions->pluck('name'))->get();
        $dbPermissions->map(function(Permission $permission) use($defaultPermissions) {
            $defaultPermission = $defaultPermissions->where('name', $permission['name'])->first();
            $permission['restrictions'] = Arr::get($defaultPermission, 'restrictions') ?: [];
            return $permission;
        });

        if (Arr::get($appRole, 'default')) {
            $attributes = ['default' => true];
            $this->role->where('name', $appRole['name'])->update(['default' => true]);
        } else if (Arr::get($appRole, 'guests')) {
            $attributes = ['guests' => true];
            $this->role->where('name', $appRole['name'])->update(['guests' => true]);
        } else {
            $attributes = ['name' => $appRole['name']];
        }

        $role = $this->role->firstOrCreate($attributes, Arr::except($appRole, ['permissions', 'extends']));
        $this->syncPermissions($role, $role->permissions->concat($dbPermissions));
        $role->save();

        return $role;
    }

    /**
     * Attach default user's role to all existing users.
     */
    private function attachUsersRoleToExistingUsers()
    {
        $role = $this->role->where('default', true)->first();

        $this->user->with('roles')->whereDoesntHave('roles', function(Builder $query) use($role) {
            return $query->where('roles.id', $role->id);
        })->select('id')->chunk(500, function(Collection $users) use($role) {
            $insert = $users->map(function(User $user) use($role) {
                return ['user_id' => $user->id, 'role_id' => $role->id, 'created_at' => Carbon::now()];
            })->toArray();
            DB::table('user_role')->insert($insert);
        });
    }
}
