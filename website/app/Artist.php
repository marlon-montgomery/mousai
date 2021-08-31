<?php namespace App;

use App\Traits\OrdersByPopularity;
use Carbon\Carbon;
use Common\Search\Searchable;
use Common\Settings\Settings;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Artist
 *
 * @property int $id
 * @property string $name
 * @property string $spotify_id
 * @property int|null $spotify_followers
 * @property int $spotify_popularity
 * @property string $image_small
 * @property string|null $image_large
 * @property int $fully_scraped
 * @property Carbon|null $updated_at
 * @property boolean $auto_update
 * @property-read Collection|Album[] $albums
 * @property-read Collection|Genre[] $genres
 * @property-read string $image_big
 * @property-read Collection|Artist[] $similar
 * @method Artist orderByPopularity(string $direction)
 * @mixin Eloquent
 */
class Artist extends Model {
    use OrdersByPopularity, Searchable, HasFactory;

    const MODEL_TYPE = 'artist';

    /**
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'spotify_popularity' => 'integer',
        'fully_scraped' => 'boolean',
        'auto_update' => 'boolean',
        'verified' => 'boolean',
    ];
    protected $appends = ['model_type'];
    protected $guarded = ['id', 'views'];
    protected $hidden = [
        'pivot',
        'spotify_followers',
        'image_large',
        'fully_scraped',
        'bio_legacy',
        'updated_at',
        'created_at',
        'wiki_image_large',
        'wiki_image_small',
        'auto_update',
        'spotify_id',
        'spotify_popularity',
        'views',
    ];

    public function albums(): BelongsToMany
    {
    	return $this->belongsToMany(Album::class, 'artist_album');
    }

    public function topTracks()
    {
        return $this->belongsToMany(Track::class)
            ->withCount('plays')
            ->orderByPopularity('desc')
            ->with(['album', 'artists' => function(BelongsToMany $builder) {
                return $builder->select('artists.name', 'artists.id');
            }])
            ->limit(20);
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class)
            ->withCount('plays')
            ->orderByPopularity('desc')
            ->with(['album', 'artists' => function(BelongsToMany $builder) {
                return $builder->select('artists.name', 'artists.id');
            }]);
    }

    public function similar()
    {
        return $this->belongsToMany(Artist::class, 'similar_artists', 'artist_id', 'similar_id')
            ->select(['artists.id', 'name', 'image_small'])
            ->orderByPopularity('desc');
    }

    public function genres(): MorphToMany
    {
        return $this->morphToMany(Genre::class, 'genreable')
            ->select('genres.name', 'genres.id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function profileImages(): HasMany
    {
        return $this->hasMany(ProfileImage::class);
    }

    public function links()
    {
        return $this->morphMany(ProfileLink::class, 'linkeable');
    }

    public function followers() {
        return $this->morphToMany(User::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    public function getImageSmallAttribute(?string $value): string
    {
        if ($value) return $value;
        return asset('client/assets/images/default/artist_small.jpg');
    }

    public function getImageLargeAttribute(?string $value): string
    {
        if ($value) return $value;
        return asset('client/assets/images/default/artist-big.jpg');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'spotify_id' => $this->spotify_id,
        ];
    }

    public function filterableFields(): array
    {
        return [
            'id',
            'spotify_id',
        ];
    }

    public function needsUpdating(): bool
    {
        if ( ! $this->exists) return false;
        $settings = app(Settings::class);
        if ($settings->get('artist_provider', 'local') === 'local') return false;
        if ( ! $this->auto_update) return false;
        if ( ! $this->fully_scraped) return true;

        $updateInterval = (int) $settings->get('automation.artist_interval', 7);

        // 0 means that artist should never be updated from 3rd party sites
        if ($updateInterval === 0) return false;

        return !$this->updated_at || $this->updated_at->addDays($updateInterval) <= Carbon::now();
    }

    public static function getModelTypeAttribute(): string
    {
        return Artist::MODEL_TYPE;
    }
}
