<?php namespace Common\Auth\Roles;

use App\User;
use Carbon\Carbon;
use Common\Auth\Permissions\Permission;
use Common\Auth\Permissions\Traits\HasPermissionsRelation;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Auth\Roles\Role
 *
 * @property integer $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property boolean $default
 * @property-read Collection|User[] $users
 * @property-read Collection|Permission[] $permissions
 * @mixin Eloquent
 * @property int $guests
 * @property string|null $legacy_permissions
 * @property string|null $description
 * @property string $type
 * @property int $internal
 * @property-read int|null $permissions_count
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 */
class Role extends Model
{
    use HasPermissionsRelation;

    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var array
     */
    protected $hidden = ['pivot', 'legacy_permissions'];

    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'default' => 'boolean', 'guests' => 'boolean'];

    /**
     * Get default role for assigning to new users.
     *
     * @return Role|null
     */
    public function getDefaultRole()
    {
        return $this->where('default', 1)->first();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withPivot('created_at');
    }
}
