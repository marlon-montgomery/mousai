<?php

namespace Common\Auth\Permissions;

use Arr;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * App\Permission
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection restrictions
 * @property string description
 * @mixin Eloquent
 * @property string $display_name
 * @property string|null $description
 * @property string $group
 * @property Collection $restrictions
 * @property string $type
 * @property int $advanced
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission query()
 */
class Permission extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'advanced' => 'integer',
    ];

    protected $hidden = ['pivot', 'permissionable_type'];

    /**
     * @param string|array $value
     * @return Collection
     */
    public function getRestrictionsAttribute($value)
    {
        // if loading permissions via parent (user, role, plan) return restrictions
        // stored on pivot table, otherwise return restrictions stored on permission itself
        $value = $this->pivot ? $this->pivot->restrictions : $value;
        if ( ! $value) $value = [];
        return collect(is_string($value) ? json_decode($value, true) : $value)->values();
    }

    public function setRestrictionsAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['restrictions'] = json_encode(array_values($value));
        }
    }

    /**
     * @param string $name
     * @return int|null
     */
    public function getRestrictionValue($name)
    {
        $restriction = $this->restrictions->first(function($restriction) use($name) {
            return $restriction['name'] === $name;
        });

        return (int) Arr::get($restriction, 'value') ?: null;
    }

    /**
     * Merge restrictions from specified permission into this permission.
     *
     * @param Permission $permission
     * @return self
     */
    public function mergeRestrictions(Permission $permission = null)
    {
        if ($permission) {
            $permission->restrictions->each(function($restriction) {
                $exists = $this->restrictions->first(function($r) use($restriction) {
                    return $r['name'] === $restriction['name'];
                });
                if ( ! $exists) {
                    $this->restrictions->push($restriction);
                }
            });
        }
        return $this;
    }
}
