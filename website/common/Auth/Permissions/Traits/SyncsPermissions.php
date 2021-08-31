<?php

namespace Common\Auth\Permissions\Traits;

use App\User;
use Common\Auth\Roles\Role;
use Common\Billing\BillingPlan;
use Illuminate\Database\Eloquent\Model;
use Arr;

trait SyncsPermissions
{
    /**
     * @param User|BillingPlan|Role|Model $model
     * @param $permissions
     */
    public function syncPermissions($model, $permissions)
    {
        $permissionIds = collect($permissions)->mapWithKeys(function($permission) {
            $restrictions = Arr::get($permission, 'restrictions', []);
            return [$permission['id'] => [
                'restrictions' => collect($restrictions)
                    ->filter(function($restriction) {
                        return isset($restriction['value']);
                    })->map(function($restriction) {
                        return ['name' => $restriction['name'], 'value' => $restriction['value']];
                    }),
            ]];
        });
        $model->permissions()->sync($permissionIds);
    }
}
