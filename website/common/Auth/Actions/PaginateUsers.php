<?php

namespace Common\Auth\Actions;

use App\User;
use Arr;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Database\Eloquent\Builder;

class PaginateUsers
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute($params): array
    {
        $query = $this->user->newQuery()->with(['roles', 'permissions']);

        if ($roleId = Arr::get($params, 'role_id')) {
            // TODO: refactor this so can reuse elsewhere maybe
            $relation = $query->getModel()->roles();
            $query
                ->leftJoin(
                    $relation->getTable(),
                    $relation->getQualifiedParentKeyName(),
                    '=',
                    $relation->getQualifiedForeignPivotKeyName(),
                )
                ->where(
                    $relation->getQualifiedRelatedPivotKeyName(),
                    '=',
                    $roleId,
                );
            $query->select(['users.*', 'user_role.created_at as created_at']);
        }

        if ($roleName = Arr::get($params, 'role_name')) {
            $query->whereHas('roles', function (Builder $q) use ($roleName) {
                $q->where('roles.name', $roleName);
            });
        }

        if ($permission = Arr::get($params, 'permission')) {
            $query
                ->whereHas('permissions', function (Builder $query) use (
                    $permission
                ) {
                    $query
                        ->where('name', $permission)
                        ->orWhere('name', 'admin');
                })
                ->orWhereHas('roles', function (Builder $query) use (
                    $permission
                ) {
                    $query->whereHas('permissions', function (
                        Builder $query
                    ) use ($permission) {
                        $query
                            ->where('name', $permission)
                            ->orWhere('name', 'admin');
                    });
                });
        }

        return (new MysqlDataSource($query, $params))->paginate()->toArray();
    }
}
