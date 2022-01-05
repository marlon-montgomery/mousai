<?php namespace App;

use Common\Auth\BaseUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\User
 *
 * @property-read Collection|Playlist[] $playlists
 * @property-read Collection|Track[] $uploadedTracks
 * @property-read UserProfile $profile
 */
class User extends BaseUser
{
    use Notifiable, HasApiTokens, HasFactory;

    const MODEL_TYPE = 'user';

    protected $appends = [
        'display_name',
        'has_password',
        'model_type',
    ];

    protected $casts = [
        'id' => 'integer',
        'available_space' => 'integer',
        'email_verified_at' => 'datetime',
        'artist_id' => 'integer',
    ];

    public function getOrCreateArtist(array $values = []): Artist {
        $primaryArtist = $this->primaryArtist();
        if ($primaryArtist) {
            return $primaryArtist;
        } else {
            return $this->artists()->create([
                'name' => $values['artist_name'] ?? $this->display_name,
                'image_small' => $values['image'] ?? $this->avatar,
                'fully_scraped' => true,
                'auto_update' => false,
            ], ['role' => 'artist']);
        }
    }

    public function primaryArtist(): ?Artist
    {
        return $this->artists()->wherePivot('role', 'artist')->first();
    }

    public function artists(): BelongsToMany {
        return $this->belongsToMany(Artist::class, 'user_artist')
            ->orderByRaw("FIELD(role, 'artist') ASC")
            ->withPivot(['role']);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function followedUsers()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->select(['users.id', 'first_name', 'last_name', 'avatar', 'email']);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
            ->select(['users.id', 'first_name', 'last_name', 'avatar', 'email']);
    }

    /**
     * @return BelongsToMany
     */
    public function likedTracks()
    {
        return $this->morphedByMany(Track::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function likedAlbums()
    {
        return $this->morphedByMany(Album::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function likedArtists()
    {
        return $this->morphedByMany(Artist::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    public function uploadedTracks(): HasMany
    {
        return $this->hasMany(Track::class, 'owner_id')
            ->whereNull('album_id')
            ->withCount('likes')
            ->withCount('reposts')
            ->orderBy('created_at', 'desc');
    }

    public function uploadedAlbums(): HasMany
    {
        return $this->hasMany(Album::class, 'owner_id')
            ->withCount('reposts')
            ->orderBy('created_at', 'desc');
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class);
    }

    /**
     * @return HasMany
     */
    public function reposts()
    {
        return $this->hasMany(Repost::class);
    }

    public function links(): MorphMany
    {
        return $this->morphMany(ProfileLink::class, 'linkeable');
    }

    public function scopeOrderByPopularity(Builder $query, $direction = 'desc')
    {
        return $query->orderBy('email', $direction);
    }

    public static function getModelTypeAttribute(): string
    {
        return User::MODEL_TYPE;
    }
}
