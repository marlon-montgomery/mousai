<?php

namespace Common\Workspaces;

use App\User;
use Carbon\Carbon;
use Common\Auth\Traits\HasAvatarAttribute;
use Common\Auth\Traits\HasDisplayNameAttribute;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\WorkspaceInvite
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Workspace workspace
 * @property int $role_id
 * @property int $workspace_id
 * @property string $email
 * @property User user
 * @mixin Eloquent
 * @property string|null $avatar
 * @property-read string $display_name
 * @property-read string $model_type
 * @property-read User|null $user
 * @property-read \Common\Workspaces\Workspace $workspace
 * @method static \Illuminate\Database\Eloquent\Builder|WorkspaceInvite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkspaceInvite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkspaceInvite query()
 */
class WorkspaceInvite extends Model
{
    use HasDisplayNameAttribute, HasAvatarAttribute;

    protected $guarded = ['id'];
    protected $appends = ['display_name', 'model_type'];

    protected $keyType = 'orderedUuid';
    public $incrementing = false;

     protected $casts = [
         'user_id' => 'integer',
     ];

     public function workspace(): BelongsTo
     {
         return $this->belongsTo(Workspace::class);
     }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getModelTypeAttribute(): string
    {
        return 'invite';
    }
}
