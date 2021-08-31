<?php

namespace Common\Files;

use App\User;
use Common\Auth\BaseUser;

/**
 * Common\Files\FileEntryUser
 *
 * @property boolean $owns_entry
 * @property array $entry_permissions
 * @property int $id
 * @property string|null $username
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $avatar_url
 * @property string|null $gender
 * @property string|null $legacy_permissions
 * @property string $email
 * @property string|null $password
 * @property string|null $card_brand
 * @property string|null $card_last_four
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property string|null $country
 * @property string|null $timezone
 * @property string|null $avatar
 * @property string|null $stripe_id
 * @property int|null $available_space
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property-read string $display_name
 * @property-read bool $has_password
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Notifications\NotificationSubscription[] $notificationSubscriptions
 * @property-read int|null $notification_subscriptions_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Auth\Permissions\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Auth\Roles\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Auth\SocialProfile[] $social_profiles
 * @property-read int|null $social_profiles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Billing\Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 * @method static Builder|BaseUser basicSearch(string $query)
 * @method static Builder|BaseUser compact()
 * @method static \Illuminate\Database\Eloquent\Builder|FileEntryUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileEntryUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileEntryUser query()
 * @method static Builder|BaseUser whereNeedsNotificationFor($notifId)
 * @mixin \Eloquent
 */
class FileEntryUser extends BaseUser
{
    protected $table = 'users';

    protected $billingEnabled = false;

    public function getMorphClass()
    {
        return User::class;
    }

    protected $hidden = [
        'password', 'remember_token', 'first_name', 'last_name', 'has_password', 'pivot'
    ];

    protected $appends = ['owns_entry', 'entry_permissions', 'display_name'];

    public function getOwnsEntryAttribute() {
        return $this->pivot->owner;
    }

    public function getEntryPermissionsAttribute() {
        return $this->pivot->permissions;
    }
}
