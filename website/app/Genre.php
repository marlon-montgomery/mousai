<?php namespace App;

use App\Traits\OrdersByPopularity;
use Arr;
use Carbon\Carbon;
use Common\Search\Searchable;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
class Genre extends Model
{
    use OrdersByPopularity, Searchable;

    const MODEL_TYPE = 'genre';
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

    public function getDisplayNameAttribute() {
        return $this->attributes['display_name'] ?? $this->attributes['name'] ?? null;
    }

    public function scopeWhereName(Builder $builder, string $name): Builder
    {
        return $builder->where('name', str_replace('-', ' ', $name))
            ->orWhere('name', $name);
    }

    /**
     * @param \Illuminate\Support\Collection|array $genres
     * @return Collection|Tag[]
     */
    public function insertOrRetrieve($genres)
    {
        if ( ! $genres instanceof Collection) {
            $genres = collect($genres);
        }

        $genres = $genres->filter()->map(function($genre) {
            if (is_string($genre)) {
                $genre = ['name' => $genre];
            }
            if ( ! Arr::get($genre, 'display_name')) {
                $genre['display_name'] = $genre['name'];
            }
            if ( ! Arr::get($genre, 'created_at')) {
                $genre['created_at'] = Carbon::now();
            }
            return $genre;
        });

        $existing = $this->whereIn('name', $genres->pluck('name'))->get();

        $new = $genres->filter(function($genre) use($existing) {
            return !$existing->first(function($existingGenre) use($genre) {
                return slugify($existingGenre['name']) === slugify($genre['name']);
            });
        });

        if ($new->isNotEmpty()) {
            $this->insert($new->toArray());
            return $this->whereIn('name', $genres->pluck('name'))->get();
        } else {
            return $existing;
        }
    }

    /**
     * @param string|null $value
     * @return string
     */
    public function getImageAttribute($value)
    {
        // default genre image
        if ( ! $value) {
            $value = "client/assets/images/default/artist_small.jpg";
        }

        // make sure image url is absolute
        if ( ! Str::contains($value, '//')) {
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

    public function filterableFields(): array
    {
        return [
            'id',
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return Genre::MODEL_TYPE;
    }
}
