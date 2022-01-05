<?php

namespace Common\Core\Policies;

use App\User;
use Common\Auth\BaseUser;
use Common\Auth\Roles\Role;
use Common\Core\Exceptions\AccessResponseWithAction;
use Common\Settings\Settings;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Str;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Settings
     */
    protected $settings;

    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    protected function userOrGuestHasPermission(?User $user, string $permission): bool
    {
        if ($user) {
            return $user->hasPermission($permission);
        } else {
            if ($guestRole = Role::where('guests', true)->first()) {
                return $guestRole->hasPermission($permission);
            }
        }
        return false;
    }

    protected function userOrGuestHasOneOfPermissions(?User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->userOrGuestHasPermission($user, $permission)) {
                return true;
            }
        }
        return false;
    }

    protected function denyWithAction($message, array $action = null): AccessResponseWithAction
    {
        /** @var AccessResponseWithAction $response */
        // TODO: use permission code here instead of passing action as code (test in belink and bedrive)
        $response = AccessResponseWithAction::deny($message, $action);
        $response->action = $action;
        return $response;
    }

    protected function storeWithCountRestriction(User $user, string $namespace): Response {
        [$relationName, $permission, $singularName, $pluralName] = $this->parseNamespace($namespace);

        // user can't create resource at all
        $response = $this->userhasPermission($user, $permission);
        if ($response->denied()) {
            return $response;
        }

        // user is admin, can ignore count restriction
        if ($user->hasPermission('admin')) {
            return Response::allow();
        }

        // user does not have any restriction on maximum resource count
        $maxCount = $user->getRestrictionValue($permission, 'count');
        if ( ! $maxCount) {
            return Response::allow();
        }

        // check if user did not go over their max quota
        if ($user->$relationName->count() >= $maxCount) {
            $message = __('policies.quota_exceeded', ['resources' => $pluralName, 'resource' => $singularName]);
            return $this->denyWithAction($message, $this->upgradeAction());
        }

        return Response::allow();
    }

    protected function userHasPermission(User $user, string $permission): Response
    {
        if ($user->hasPermission($permission)) {
            return Response::allow();
        } else {
            return Response::deny();
        }
    }

    protected function parseNamespace(string $namespace, string $ability = 'create'): array
    {
        // 'App\SomeModel' => 'Some_Model'
        $resourceName = Str::snake(class_basename($namespace));

        // 'Some_Model' => 'someModels'
        $relationName = Str::camel(Str::plural($resourceName));

        // 'Some_Model' => 'Some Model'
        $singularName = str_replace('_', ' ', $resourceName);

        // 'Some Model' => 'Some Models'
        $pluralName = Str::plural($singularName);

        // parent might need to override permission name. custom_domains instead of links_domains for example.
        $permissionName = $this->permissionName ?? Str::snake($relationName);

        return [$relationName, "$permissionName.$ability", $singularName, $pluralName];
    }

    protected function upgradeAction(): ?array
    {
        if ($this->settings->get('billing.enable')) {
            return ['label' => 'Upgrade', 'action' => '/billing/upgrade'];
        } else {
            return null;
        }
    }
}
