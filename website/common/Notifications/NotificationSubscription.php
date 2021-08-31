<?php

namespace Common\Notifications;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Common\Notifications\NotificationSubscription
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string notif_id
 * @property array channels
 * @mixin Eloquent
 * @property string $notif_id
 * @property array $channels
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationSubscription query()
 */
class NotificationSubscription extends Model
{
    protected $guarded = ['id'];
    protected $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'user_id' => 'integer',
        'channels' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }
}
