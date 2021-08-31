<?php namespace Common\Files;

use App\User;
use Auth;
use Carbon\Carbon;
use Common\Files\Traits\HandlesEntryPaths;
use Common\Files\Traits\HashesId;
use Common\Search\Searchable;
use Common\Tags\HandlesTags;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Arr;
use Storage;

/**
 * FileEntry
 *
 * @mixin Eloquent
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $file_name
 * @property string $file_size
 * @property string $mime
 * @property string $extension
 * @property boolean $thumbnail
 * @property string $preview_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $type
 * @property-read FileEntry|null $parent
 * @property-read Collection $users
 * @property-read User $owner
 * @property string $path
 * @property string $disk_prefix
 * @property boolean $public
 * @method static \Illuminate\Database\Query\Builder|FileEntry onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|FileEntry rootOnly()
 * @method static \Illuminate\Database\Query\Builder|FileEntry onlyStarred()
 * @method static \Illuminate\Database\Query\Builder|FileEntry whereRootOrParentNotTrashed()
 * @method whereOwner($userId)
 * @property int $owner_id
 * @property string|null $description
 * @property string|null $password
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Collection|FileEntry[] $children
 * @property-read int|null $children_count
 * @property-read string $hash
 * @property-read string|null $url
 * @property-read int|null $users_count
 * @method static Builder|FileEntry allChildren()
 * @method static Builder|FileEntry allParents()
 * @method static Builder|FileEntry basicSearch(string $query)
 * @method static Builder|FileEntry newModelQuery()
 * @method static Builder|FileEntry newQuery()
 * @method static Builder|FileEntry query()
 * @method static Builder|FileEntry whereHash($value)
 * @method static Builder|FileEntry whereNotOwner($userId)
 * @method static Builder|FileEntry whereUser($userId, $owner = null)
 * @method static \Illuminate\Database\Query\Builder|FileEntry withTrashed()
 * @method static \Illuminate\Database\Query\Builder|FileEntry withoutTrashed()
 * @property-read Collection|Tag[] $tags
 * @property-read int|null $tags_count
 */
class FileEntry extends Model
{
    use SoftDeletes, HashesId, HandlesEntryPaths, HandlesTags, Searchable;

    protected $guarded = ['id'];
    protected $hidden  = ['pivot', 'preview_token'];
    protected $appends = ['hash', 'url'];
    protected $casts = [
        'id' => 'integer',
        'file_size' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
        'thumbnail' => 'boolean',
        'public' => 'boolean',
        'workspace_id' => 'integer',
    ];

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(FileEntryUser::class, 'model', 'file_entry_models', 'file_entry_id', 'model_id')
            ->using(FileEntryPivot::class)
            ->select('first_name', 'last_name', 'email', 'users.id', 'avatar')
            ->withPivot('owner', 'permissions')
            ->withTimestamps()
            ->orderBy('file_entry_models.created_at');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')->withoutGlobalScope('fsType');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->wherePivot('user_id', Auth::id() ?? null);
    }

    public function getUrlAttribute(string $value = null): ?string
    {
        if ($value) return $value;
        if ( ! isset($this->attributes['type']) || $this->attributes['type'] === 'folder') {
            return null;
        }

        if (Arr::get($this->attributes, 'public')) {
            return Storage::disk('public')->url("$this->disk_prefix/$this->file_name");
        } else if ($endpoint = config('common.site.file_preview_endpoint')) {
           return "$endpoint/uploads/{$this->file_name}/{$this->file_name}";
        } else {
            return 'secure/uploads/'.$this->attributes['id'];
        }
    }

    public function getStoragePath(bool $useThumbnail = false): string
    {
        $fileName = $useThumbnail ? 'thumbnail.jpg' : $this->file_name;
        if ($this->public) {
            return "$this->disk_prefix/$fileName";
        } else {
            return "$this->file_name/$fileName";
        }
    }

    public function getDisk()
    {
        if ($this->public) {
            return Storage::drive('public');
        } else {
            return Storage::drive('uploads');
        }
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeWhereRootOrParentNotTrashed(Builder $query)
    {
        return $query->whereNull('parent_id')
            ->orWhereHas('parent', function(Builder $query) {
                return $query->whereNull('deleted_at');
            });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Select all entries user has access to.
     *
     * @param Builder $builder
     * @param $userId
     * @param bool|null $owner
     * @return Builder
     */
    public function scopeWhereUser(Builder $builder, $userId, $owner = null) {
        return $builder->whereIn($this->qualifyColumn('id'), function ($query) use($userId, $owner) {
            $query->select('file_entry_id')
                ->from('file_entry_models')
                ->where('model_id', $userId)
                ->where('model_type', User::class);

            // if $owner is not null, need to load either only
            // entries user owns or entries user does not own
            //if $owner is null, load all entries
            if ( ! is_null($owner)) {
                $query->where('owner', $owner);
            }
        });
    }

    /**
     * Select only entries that were created by specified user.
     *
     * @param Builder $builder
     * @param $userId
     * @return Builder
     */
    public function scopeWhereOwner(Builder $builder, $userId) {
        return $builder->where('owner_id', $userId);
    }

    /**
     * Select only entries that were not created by specified user.
     *
     * @param Builder $builder
     * @param $userId
     * @return Builder
     */
    public function scopeWhereNotOwner(Builder $builder, $userId) {
        // TODO: maybe refactor this (owner_id was materialized on main table, but still need to fetch all files user is attached to but is not owner of)
        return $this->scopeWhereUser($builder, $userId, false);
    }

    /**
     * Get path of specified entry.
     *
     * @param int $id
     * @return string
     */
    public function findPath($id)
    {
        $entry = $this->find($id, ['path']);
        return $entry ? $entry->getRawOriginal('path') : '';
    }

    /**
     * Return file entry name with extension.
     * @return string
     */
    public function getNameWithExtension() {
        if ( ! $this->exists) return '';

        $extension = pathinfo($this->name, PATHINFO_EXTENSION);

        if ( ! $extension && $this->extension) {
            return $this->name .'.'. $this->extension;
        }

        return $this->name;
    }

    public function getTotalSize(): int
    {
        if ($this->type === 'folder') {
            return $this->allChildren()->sum('file_size');
        } else {
            return $this->file_size;
        }
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_size' => $this->file_size,
            'mime' => $this->mime,
            'extension' => $this->extension,
            'owner_id' => $this->owner_id,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
            'deleted_at' => $this->deleted_at->timestamp ?? '_null',
            'public' => $this->public,
            'description' => $this->description,
            'password' => $this->password,
            'type' => $this->type,
            'workspace_id' => $this->workspace_id ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'id',
            'owner_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'file_size',
            'public',
            'password',
            'type',
            'workspace_id',
        ];
    }
}
