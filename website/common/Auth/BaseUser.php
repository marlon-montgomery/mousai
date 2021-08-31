<?php namespace Common\Auth;

use App\User;
use Arr;
use Auth;
use Carbon\Carbon;
use Common\Auth\Permissions\Permission;
use Common\Auth\Permissions\Traits\HasPermissionsRelation;
use Common\Auth\Roles\Role;
use Common\Auth\Traits\HasAvatarAttribute;
use Common\Auth\Traits\HasDisplayNameAttribute;
use Common\Billing\Billable;
use Common\Billing\BillingPlan;
use Common\Files\FileEntry;
use Common\Files\FileEntryPivot;
use Common\Files\Traits\SetsAvailableSpaceAttribute;
use Common\Notifications\NotificationSubscription;
use Common\Search\Searchable;
use Common\Settings\Settings;
use Eloquent;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string|null $username
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $gender
 * @property-read Collection|Permission[] $permissions
 * @property string $email
 * @property string $password
 * @property integer|null $available_space
 * @property string|null $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $stripe_active
 * @property string|null $stripe_id
 * @property string|null $stripe_subscription
 * @property string|null $stripe_plan
 * @property string|null $last_four
 * @property string|null $trial_ends_at
 * @property string|null $subscription_ends_at
 * @property string $avatar
 * @property-read string $display_name
 * @property-read mixed $followers_count
 * @property-read bool $has_password
 * @property-read Collection|Role[] $roles
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read NotificationSubscription[]|Collection $notificationSubscriptions
 * @method BaseUser compact()
 * @method Builder whereNeedsNotificationFor(string $eventId)
 * @mixin Eloquent
 */
abstract class BaseUser extends Authenticatable
{
    use Searchable,
        Notifiable,
        Billable,
        SetsAvailableSpaceAttribute,
        HasPermissionsRelation,
        HasAvatarAttribute,
        HasDisplayNameAttribute;

    // prevent avatar from being set along with other user details
    protected $guarded = ['id', 'avatar'];
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
        'legacy_permissions',
    ];
    protected $casts = [
        'id' => 'integer',
        'available_space' => 'integer',
        'email_verified_at' => 'datetime',
    ];
    protected $appends = ['display_name', 'has_password'];
    protected $billingEnabled = true;
    protected $gravatarSize;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->billingEnabled = app(Settings::class)->get('billing.enable');
    }

    public function toArray()
    {
        if (
            !Auth::id() ||
            (Auth::id() !== $this->id &&
                !Auth::user()->hasPermission('users.update'))
        ) {
            $this->hidden = array_merge($this->hidden, [
                'first_name',
                'last_name',
                'avatar_url',
                'gender',
                'email',
                'card_brand',
                'has_password',
                'confirmed',
                'stripe_id',
                'roles',
                'permissions',
                'card_last_four',
                'created_at',
                'updated_at',
                'available_space',
                'email_verified_at',
                'timezone',
                'confirmation_code',
                'subscriptions',
            ]);
        }
        return parent::toArray();
    }

    /**
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * @return string
     */
    public function routeNotificationForSlack()
    {
        return config('services.slack.webhook_url');
    }

    /**
     * @param Builder $query
     * @param string $notifId
     * @return Builder
     */
    public function scopeWhereNeedsNotificationFor(Builder $query, $notifId)
    {
        return $query->whereHas('notificationSubscriptions', function (
            Builder $builder
        ) use ($notifId) {
            if (\Str::contains($notifId, '*')) {
                return $builder->where(
                    'notif_id',
                    'like',
                    str_replace('*', '%', $notifId),
                );
            } else {
                return $builder->where('notif_id', $notifId);
            }
        });
    }

    /**
     * @return HasMany
     */
    public function notificationSubscriptions()
    {
        return $this->hasMany(NotificationSubscription::class);
    }

    /**
     * @param array $options
     * @return BelongsToMany
     */
    public function entries($options = ['owner' => true])
    {
        $query = $this->morphToMany(
            FileEntry::class,
            'model',
            'file_entry_models',
            'model_id',
            'file_entry_id',
        )
            ->using(FileEntryPivot::class)
            ->withPivot('owner', 'permissions');

        if (Arr::get($options, 'owner')) {
            $query->wherePivot('owner', true);
        }

        return $query
            ->withTimestamps()
            ->orderBy('file_entry_models.created_at', 'asc');
    }

    /**
     * Social profiles this users account is connected to.
     *
     * @return HasMany
     */
    public function social_profiles()
    {
        return $this->hasMany(SocialProfile::class);
    }

    /**
     * Check if user has a password set.
     *
     * @return bool
     */
    public function getHasPasswordAttribute()
    {
        return isset($this->attributes['password']) &&
            $this->attributes['password'];
    }

    /**
     * @return self
     */
    public function loadPermissions($force = false)
    {
        if (!$force && $this->relationLoaded('permissions')) {
            return $this;
        }

        $query = app(Permission::class)
            ->join(
                'permissionables',
                'permissions.id',
                'permissionables.permission_id',
            )
            ->where([
                'permissionable_id' => $this->id,
                'permissionable_type' => User::class,
            ]);

        if ($this->roles->pluck('id')->isNotEmpty()) {
            $query->orWhere(function (Builder $builder) {
                return $builder
                    ->whereIn('permissionable_id', $this->roles->pluck('id'))
                    ->where('permissionable_type', Role::class);
            });
        }

        if ($plan = $this->getBillingPlan(true)) {
            $query->orWhere(function (Builder $builder) use ($plan) {
                return $builder
                    ->where('permissionable_id', $plan->id)
                    ->where('permissionable_type', BillingPlan::class);
            });
        }

        $permissions = $query
            ->select([
                'permissions.id',
                'name',
                'permissionables.restrictions',
                'permissionable_type',
            ])
            ->get()
            ->sortBy(function ($value) {
                if ($value['permissionable_type'] === User::class) {
                    return 1;
                } elseif (
                    $value['permissionable_type'] === BillingPlan::class
                ) {
                    return 2;
                } else {
                    return 3;
                }
            })
            ->groupBy('id')

            // merge restrictions from all permissions
            ->map(function (Collection $group) {
                return $group->reduce(function (
                    Permission $carry,
                    Permission $permission
                ) {
                    return $carry->mergeRestrictions($permission);
                },
                $group[0]);
            });

        $this->setRelation('permissions', $permissions->values());

        return $this;
    }

    public function getBillingPlan($parent = false): ?BillingPlan
    {
        if (!$this->billingEnabled) {
            return null;
        }

        $subscription = $this->subscriptions->first();

        if ($subscription && $subscription->valid()) {
            return $parent ? $subscription->mainPlan() : $subscription->plan;
        } else {
            return BillingPlan::where('free', true)->first();
        }
    }

    /**
     * @param string $permissionName
     * @param string $restriction
     * @return int|null
     */
    public function getRestrictionValue($permissionName, $restriction)
    {
        $permission = $this->getPermission($permissionName);
        return $permission
            ? $permission->getRestrictionValue($restriction)
            : null;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompact(Builder $query)
    {
        return $query->select(
            'users.id',
            'users.avatar',
            'users.email',
            'users.first_name',
            'users.last_name',
            'users.username',
        );
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        ResetPassword::$createUrlCallback = function ($user, $token) {
            return url("password/reset/$token");
        };
        $this->notify(new ResetPassword($token));
    }

    public static function findAdmin(): ?self
    {
        return (new static())
            ->whereHas('permissions', function (Builder $query) {
                $query->where('name', 'admin');
            })
            ->first();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'created_at',
            'updated_at',
        ];
    }
}
