<?php namespace Common\Pages;

use App\User;
use Carbon\Carbon;
use Common\Search\Searchable;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Page
 *
 * @property int $id
 * @property string $body
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int user_id
 * @mixin Eloquent
 * @property string|null $title
 * @property string|null $meta
 * @property string $type
 * @property int|null $user_id
 * @property int|null $workspace_id
 * @property bool $hide_nav
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read User|null $user
 * @method static Builder|CustomPage basicSearch(string $query)
 * @method static Builder|CustomPage newModelQuery()
 * @method static Builder|CustomPage newQuery()
 * @method static Builder|CustomPage query()
 */
class CustomPage extends Model
{
    use Searchable;

    const PAGE_TYPE = 'default';
    const MODEL_TYPE = 'customPage';

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'hide_nav' => 'boolean',
    ];

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = slugify($value);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'slug' => $this->slug,
            'type' => $this->type,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
            'user_id' => $this->user_id,
            'workspace_id' => $this->workspace_id ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'user_id',
            'created_at',
            'updated_at',
            'type',
            'workspace_id',
        ];
    }
}
