<?php namespace App;

use App\Traits\OrdersByPopularity;
use Carbon\Carbon;
use Common\Comments\Comment;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Common\Search\Searchable;
use Storage;

/**
 * App\Track
 *
 * @property int $id
 * @property string $name
 * @property string $album_name
 * @property int $number
 * @property int $duration
 * @property string|null $youtube_id
 * @property int $spotify_popularity
 * @property int $album_id
 * @property string|null $temp_id
 * @property boolean $auto_update
 * @property string|null $url
 * @property-read Album $album
 * @property-read Collection|Playlist[] $playlists
 * @property-read Collection|User[] $users
 * @method Track orderByPopularity(string $direction)
 * @property string image
 * @property Collection|Artist[] artists
 * @property int owner_id
 * @mixin Eloquent
 */
class Track extends Model {
    use OrdersByPopularity, Searchable, HasFactory;

    const MODEL_TYPE = 'track';

    protected $guarded = [
        'id',
        'formatted_duration',
        'plays',
        'lyric'
    ];

    protected $hidden = [
        'fully_scraped',
        'temp_id',
        'pivot',
        'artists_legacy',
        'album_name',
        'album_id',
        'auto_update',
        'local_only',
        'spotify_id',
        'updated_at',
        'user_id',
        'description',
    ];

    protected $casts = [
        'id'       => 'integer',
        'album_id' => 'integer',
        'number'   => 'integer',
        'spotify_popularity' => 'integer',
        'duration' => 'integer',
        'auto_update' => 'boolean',
        'position' => 'integer',
    ];

    protected $appends = ['model_type'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ( ! request()->isFromFrontend()) {
            $this->hidden[] = 'url';
            $this->hidden[] = 'youtube_id';
            $this->hidden[] = 'spotify_popularity';
        }
    }

    /**
     * @return BelongsToMany
     */
    public function likes()
    {
        return $this->morphToMany(User::class, 'likeable', 'likes')
            ->withTimestamps();
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
     * @return BelongsTo
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class)
            ->select(['artists.id', 'artists.name', 'artists.image_small']);
    }

    public function plays()
    {
        return $this->hasMany(TrackPlay::class);
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
            ->select('genres.name', 'genres.display_name', 'genres.id');
    }

    /**
     * @return BelongsToMany
     */
    public function playlists()
    {
        return $this->belongsToMany('App\Playlist')->withPivot('position');
    }

    /**
     * @return HasOne
     */
    public function lyric()
    {
        return $this->hasOne('App\Lyric');
    }

    public function getCreatedAtAttribute(?string $date): ?string
    {
        return $date ? Carbon::parse($date)->diffForHumans() : null;
    }

    /**
     * @return FilesystemAdapter
     */
    public function getWaveStorageDisk()
    {
        return Storage::disk(config('common.site.wave_storage_disk'));
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
            'album_name' => $this->album_name,
            'spotify_id' => $this->spotify_id,
            'artists' => $this->artists->pluck('name'),
        ];
    }

    protected function makeAllSearchableUsing($query)
    {
        return $query->with('artists');
    }

    public function filterableFields(): array
    {
        return [
            'id',
            'spotify_id',
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return Track::MODEL_TYPE;
    }
}
