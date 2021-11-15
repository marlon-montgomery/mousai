<?php

namespace Common\Workspaces;

use Auth;
use Common\Auth\Permissions\Permission;
use Common\Auth\Roles\Role;
use Common\Auth\Traits\HasAvatarAttribute;
use Common\Auth\Traits\HasDisplayNameAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Workspaces\WorkspaceMember
 *
 * @property int $id
 * @property Workspace workspace
 * @property Permission[]|Collection permissions
 * @property boolean is_owner
 * @property int $user_id
 * @property int $workspace_id
 * @property int|null $role_id
 * @property bool $is_owner
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $avatar
 * @property-read string $display_name
 * @property-read string $model_type
 * @property-read mixed $role_name
 * @property-read Collection|Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Common\Workspaces\Workspace $workspace
 * @method static Builder|WorkspaceMember currentUserAndOwnerOnly()
 * @method static Builder|WorkspaceMember newModelQuery()
 * @method static Builder|WorkspaceMember newQuery()
 * @method static Builder|WorkspaceMember query()
 * @mixin \Eloquent
 */
class WorkspaceMember extends Model
{
    use HasAvatarAttribute, HasDisplayNameAttribute;

    protected $table = 'workspace_user';
    protected $guarded = ['id'];
    protected $appends = ['display_name', 'model_type'];
    protected $casts = ['is_owner' => 'boolean'];

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'permissionables', 'permissionable_id', 'permission_id', 'role_id')
            ->where('permissionable_type', Role::class)
            ->select(['permissions.id', 'permissions.name', 'permissions.restrictions']);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function scopeCurrentUserAndOwnerOnly(Builder $builder): self
    {
        $builder->where(function(Builder $builder) {
            $builder->where('workspace_user.user_id', Auth::id())
                ->orWhere('workspace_user.is_owner', true);
        });

        return $this;
    }

    public static function getModelTypeAttribute(): string
    {
        return 'member';
    }

    public function hasPermission(string $name): bool
    {
        return $this->is_owner || !is_null($this->getPermission($name));
    }

    public function getPermission(string $name): ?Permission
    {
        return $this->permissions->first(function(Permission $permission) use($name) {
            return $permission->name === $name;
        });
    }

    public function getRoleNameAttribute() {
        return $this->is_owner ? 'Workspace Owner' : $this->attributes['role_name'];
    }
}
