<?php namespace Common\Billing;

use Common\Auth\Permissions\Permission;
use Carbon\Carbon;
use Common\Auth\Permissions\Traits\HasPermissionsRelation;
use Common\Files\Traits\SetsAvailableSpaceAttribute;
use Common\Search\Searchable;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Common\Billing\BillingPlan
 *
 * @property int $id
 * @property string $name
 * @property int $amount
 * @property string $currency
 * @property string $interval
 * @property string $interval_count
 * @property integer $parent_id
 * @property boolean $free
 * @property integer $available_space
 * @property string $uuid
 * @property string $paypal_id
 * @property string $features
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read BillingPlan $parent
 * @property-read Collection|Permission[] $permissions
 * @mixin Eloquent
 * @property string $currency_symbol
 * @property string|null $legacy_permissions
 * @property bool $recommended
 * @property bool $show_permissions
 * @property int $position
 * @property bool $hidden
 * @property-read int|null $permissions_count
 * @property-read Collection|\Common\Billing\Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BillingPlan query()
 */
class BillingPlan extends Model
{
    use HasPermissionsRelation, SetsAvailableSpaceAttribute, Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'float',
        'interval_count' => 'integer',
        'recommended' => 'boolean',
        'free' => 'boolean',
        'show_permissions' => 'boolean',
        'position' => 'integer',
        'available_space' => 'integer',
        'parent_id' => 'integer',
        'hidden' => 'boolean',
    ];

    public function getFeaturesAttribute($value)
    {
        if ($this->parent_id && $this->parent_id !== $this->id && $this->parent) {
            return $this->parent->features;
        }

        return json_decode($value, true) ?: [];
    }

    public function setFeaturesAttribute($value)
    {
        if (is_string($value)) return;
        $this->attributes['features'] = json_encode($value);
    }

    public function parent()
    {
        return $this->belongsTo(BillingPlan::class, 'parent_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'currency' => $this->currency,
            'features' => $this->features,
            'interval' => $this->interval,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'currency',
            'interval',
            'parent_id',
            'created_at',
            'updated_at',
        ];
    }
}
