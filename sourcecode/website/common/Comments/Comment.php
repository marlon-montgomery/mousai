<?php

namespace Common\Comments;

use App\User;
use Common\Files\Traits\HandlesEntryPaths;
use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Comment
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string content
 * @method Comment rootOnly()
 * @method Comment childrenOnly()
 * @mixin Eloquent
 * @property int|null $parent_id
 * @property string $path
 * @property int $commentable_id
 * @property string $commentable_type
 * @property-read Model|\Eloquent $commentable
 * @property-read mixed $depth
 * @property-read User $user
 * @method static Builder|Comment allChildren()
 * @method static Builder|Comment allParents()
 * @method static Builder|Comment newModelQuery()
 * @method static Builder|Comment newQuery()
 * @method static Builder|Comment query()
 * @property string $content
 * @property bool $deleted
 * @method static \Common\Comments\CommentFactory factory(...$parameters)
 */
class Comment extends Model
{
    use HandlesEntryPaths, HasFactory;

    const MODEL_TYPE = 'comment';

    protected $guarded = ['id'];

    protected $hidden = [
        'commentable_type',
        'commentable_id',
        'path',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'deleted' => 'boolean'
    ];

    protected $appends = ['depth'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo {
        return $this->morphTo();
    }

    public function scopeRootOnly(Builder $builder)
    {
        return $builder->whereNull('parent_id');
    }

    public function scopeChildrenOnly(Builder $builder)
    {
        return $builder->whereNotNull('parent_id');
    }

    public function getDepthAttribute()
    {
        return substr_count($this->getRawOriginal('path'), '/');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'deleted' => $this->deleted,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'parent_id',
            'user_id',
            'deleted',
            'commentable_id',
            'commentable_type',
            'created_at',
            'updated_at',
        ];
    }

    protected static function newFactory()
    {
        return CommentFactory::new();
    }
}
