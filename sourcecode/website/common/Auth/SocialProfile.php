<?php namespace Common\Auth;

use App\User;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\SocialProfile
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $service_name
 * @property string $user_service_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read ?User $user
 * @method static Builder|SocialProfile whereId($value)
 * @mixin Eloquent
 * @property string $username
 * @property ?Carbon $access_expires_at
 * @property string $refresh_token
 * @property string $access_token
 * @method static Builder|SocialProfile newModelQuery()
 * @method static Builder|SocialProfile newQuery()
 * @method static Builder|SocialProfile query()
 */
class SocialProfile extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['access_expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
