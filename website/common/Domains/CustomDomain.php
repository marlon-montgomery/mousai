<?php

namespace Common\Domains;

use App\User;
use Common\Search\Searchable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Common\Domains\CustomDomain
 *
 * @property string $host // host with protocol already prefixed
 * @property int resource_id
 * @method Builder forUser(int $userId)
 * @mixin Eloquent
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $global
 * @property int|null $resource_id
 * @property string|null $resource_type
 * @property int|null $workspace_id
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $resource
 * @property-read \App\User $user
 * @method static Builder|CustomDomain basicSearch(string $query)
 * @method static \Illuminate\Database\Eloquent\Builder|LinkDomain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LinkDomain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LinkDomain query()
 */
class CustomDomain extends Model
{
    use Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'global' => 'boolean',
        'resource_id' => 'int',
    ];

    const MODEL_TYPE = 'customDomain';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resource(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Limit query to only custom domains specified user has access to.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->orWhere('global', true);
    }

    public function getHostAttribute(?string $value): ?string
    {
        return parse_url($value, PHP_URL_SCHEME) === null
            ? "https://$value"
            : $value;
    }

    public function setHostAttribute(string $value)
    {
        $this->attributes['host'] = trim($value, '/');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'host' => $this->host,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
            'global' => $this->global,
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
            'global',
            'workspace_id',
        ];
    }
}
