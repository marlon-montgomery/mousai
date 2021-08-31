<?php namespace App;

use App\Traits\OrdersByPopularity;
use Carbon\Carbon;
use Common\Search\Searchable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Str;

/**
 * App\Playlist
 *
 * @property int $id
 * @property string $name
 * @property string $image
 * @property int $public
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Track[] $tracks
 * @method static Builder|Playlist whereCreatedAt($value)
 * @method static Builder|Playlist whereId($value)
 * @method static Builder|Playlist whereName($value)
 * @method static Builder|Playlist wherePublic($value)
 * @method static Builder|Playlist whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|User[] $editors
 */
class Playlist extends Model {
    use OrdersByPopularity, Searchable;

    const MODEL_TYPE = 'playlist';

    protected $guarded = ['id', 'owner_id'];
    protected $hidden = [
        'pivot',
        'updated_at',
        'spotify_id',
        'description',
        'views',
    ];
    protected $appends = ['model_type'];

    protected $casts = [
        'id' => 'integer',
        'owner_id' => 'integer',
        'public' => 'boolean',
        'collaborative' => 'boolean',
    ];

    public function getImageAttribute($value)
    {
        if ( ! $value || Str::contains($value, 'images/default')) return null;
        return $value;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->where('editor', true)
            ->withPivotValue(['editor' => true])
            ->compact();
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class);
    }

    public function getCreatedAtAttribute(?string $date): ?string
    {
        return $date ? Carbon::parse($date)->diffForHumans() : null;
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->exists && $this->public;
    }

    public function filterableFields(): array
    {
        return [
            'id',
        ];
    }


    public static function getModelTypeAttribute(): string
    {
        return Playlist::MODEL_TYPE;
    }
}
