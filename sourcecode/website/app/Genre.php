<?php namespace App;

use App\Traits\OrdersByPopularity;
use Carbon\Carbon;
use Common\Search\Searchable;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Str;

/**
 * App\Genre
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Artist[] $artists
 * @mixin Eloquent
 */
class Genre extends Tag
{
    use OrdersByPopularity, Searchable;

    const MODEL_TYPE = 'genre';
    protected $table = 'genres';
    protected $guarded = ['id'];
    protected $hidden = ['pivot'];
    protected $appends = ['model_type'];

    public function artists(): MorphToMany
    {
        return $this->morphedByMany(Artist::class, 'genreable');
    }

    public function tracks(): MorphToMany
    {
        return $this->morphedByMany(Track::class, 'genreable');
    }

    public function albums(): MorphToMany
    {
        return $this->morphedByMany(Album::class, 'genreable');
    }

    public function insertOrRetrieve($tags, ?string $type = null)
    {
        return parent::insertOrRetrieve($tags, $type);
    }

    /**
     * @param string|null $value
     * @return string
     */
    public function getImageAttribute($value)
    {
        // default genre image
        if (!$value) {
            $value = 'client/assets/images/default/artist_small.jpg';
        }

        // make sure image url is absolute
        if (!Str::contains($value, '//')) {
            $value = url($value);
        }

        return $value;
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
        ];
    }

    public static function filterableFields(): array
    {
        return ['id'];
    }

    public static function getModelTypeAttribute(): string
    {
        return Genre::MODEL_TYPE;
    }
}
