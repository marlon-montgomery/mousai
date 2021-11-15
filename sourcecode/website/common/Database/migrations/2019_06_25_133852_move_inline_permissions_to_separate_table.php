<?php

use App\User;
use Common\Auth\Permissions\Permission;
use Common\Auth\Roles\Role;
use Common\Billing\BillingPlan;
use Common\Core\Values\GetStaticPermissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;

class MoveInlinePermissionsToSeparateTable extends Migration
{
    /**
     * @var array
     */
    private $staticPermissions;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $models = [Role::class, User::class, BillingPlan::class];
        $this->staticPermissions = collect(app(GetStaticPermissions::class)->execute())->toArray();

        foreach ($models as $model) {
            app($model)
                ->whereNotNull('legacy_permissions')
                ->orderBy('id')
                ->chunk(50, function(Collection $models) {
                    $models->each(function($model) {
                        try {
                            $permissions = array_keys(json_decode($model->legacy_permissions, true));
                        } catch (Exception $e) {
                           return;
                        }
                        $permissions = collect($permissions)->map(function($permissionName) {
                            if ($existing = app(Permission::class)->where('name', $permissionName)->first()) {
                                return $existing;
                            } else {
                                $permissionConfig = $this->getPermissionConfig($permissionName);
                                if ( ! $permissionConfig) {
                                    $permissionConfig = ['name' => $permissionName];
                                }
                                return app(Permission::class)->create($permissionConfig);
                            }
                        })->filter();
                        $model->permissions()->syncWithoutDetaching($permissions->pluck('id'));
                    });
                });
        }
    }

    private function getPermissionConfig($name)
    {
        foreach ($this->staticPermissions as $groupName => $group) {
            foreach ($group as $permission) {
                if ($permission['name'] === $name) {
                    $permission['group'] = $groupName;
                    return $permission;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
