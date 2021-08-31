<?php namespace App;

use App\Traits\OrdersByPopularity;
use Carbon\Carbon;
use Common\Comments\Comment;
use Common\Settings\Settings;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Common\Search\Searchable;

/**
 * App\Album
 *
 * @property int $id
 * @property string $name
 * @property string|null $release_date
 * @property string $image
 * @property int $spotify_popularity
 * @property int $fully_scraped
 * @property string|null $temp_id
 * @property boolean $auto_update
 * @property-read Collection|Artist[] $artists
 * @property-read Collection|Track[] $tracks
 * @property string spotify_id
 * @property int owner_id
 * @mixin Eloquent
 */
class Album extends Model {

    use OrdersByPopularity, Searchable, HasFactory;

    const MODEL_TYPE = 'album';

    /**
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'fully_scraped' => 'boolean',
        'spotify_popularity' => 'integer',
        'auto_update' => 'boolean',
        'owner_id' => 'integer',
    ];
    
    protected $guarded = ['id', 'views'];
    protected $hidden = [
        'pivot',
        'fully_scraped',
        'temp_id',
        'artist_id',
        'auto_update',
        'views',
        'local_only',
        'spotify_id',
        'description',
        'artist_type',
        'updated_at',
    ];
    protected $appends = ['model_type'];

    public function artists(): BelongsToMany
    {
    	return $this->belongsToMany(Artist::class, 'artist_album')
            ->select(['artists.id', 'artists.name', 'artists.image_small'])
    	    ->orderBy('artist_album.primary', 'desc');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return MorphMany
     */
    public function reposts()
    {
        return $this->morphMany(Repost::class, 'repostable');
    }

    /**
     * @return BelongsToMany
     */
    public function likes()
    {
        return $this->morphToMany(User::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function tracks()
    {
    	return $this->hasMany(Track::class, 'album_id')
            ->orderBy('number');
    }

    /**
     * @return HasManyThrough
     */
    public function plays()
    {
        return $this->hasManyThrough(TrackPlay::class, Track::class);
    }

    /**
     * @return MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->select('tags.name', 'tags.display_name', 'tags.id');
    }

    /**
     * @return MorphToMany
     */
    public function genres()
    {
        return $this->morphToMany(Genre::class, 'genreable')
            ->select('genres.name', 'genres.id');
    }

    public function needsUpdating(): bool
    {
        if ( ! $this->exists || ! $this->spotify_id || ! $this->auto_update) return false;
        if (app(Settings::class)->get('album_provider', 'local') === 'local') return false;

        if ( ! $this->fully_scraped) return true;
        if ( ! $this->tracks || $this->tracks->isEmpty()) return true;

        return false;
    }

    public function getCreatedAtAttribute(?string $date): ?string
    {
        return $date ? Carbon::parse($date)->diffForHumans() : null;
    }

    public function addPopularityToTracks()
    {
        $settings = app(Settings::class);
        $highestPlaysCount = $this->tracks->pluck('plays')->max();

        $this->tracks->map(function (Track $track) use($highestPlaysCount, $settings) {
            if ($settings->get('artist_provider') === 'spotify') {
                $track->popularity = $track->spotify_popularity ?: 50;
            } else if ($highestPlaysCount) {
                $track->popularity = $track->plays / ($highestPlaysCount * 50);
            } else {
                $track->popularity = 50;
            }
            return $track;
        });
    }

    public function toNormalizedArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'spotify_id' => $this->spotify_id,
            'artists' => $this->artists->pluck('name'),
        ];
    }

    public function filterableFields(): array
    {
        return [
            'id',
            'spotify_id',
        ];
    }

    protected function makeAllSearchableUsing($query)
    {
        return $query->with('artists');
    }

    public static function getModelTypeAttribute(): string
    {
        return Album::MODEL_TYPE;
    }
}
