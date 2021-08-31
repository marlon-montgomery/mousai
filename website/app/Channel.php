<?php

namespace App;

use App\Actions\Channel\LoadChannelContent;
use Carbon\Carbon;
use Common\Search\Searchable;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Channel
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string $auto_update
 * @property array $config
 * @property string content_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin Eloquent
 */
class Channel extends Model
{
    use Searchable;

    const MODEL_TYPE = "channel";
    protected $guarded = ["id"];
    protected $appends = ["model_type"];
    protected $hidden = ["pivot"];

    protected $casts = [
        "id" => "integer",
        "user_id" => "integer",
        "hide_title" => "boolean",
    ];
    protected static function booted()
    {
        // touch parent channels
        static::updated(function (Channel $channel) {
            $parentIds = DB::table("channelables")
                ->where("channelable_type", Channel::class)
                ->where("channelable_id", $channel->id)
                ->pluck("channel_id");
            Channel::withoutEvents(function () use ($parentIds) {
                Channel::whereIn("id", $parentIds)->update([
                    "updated_at" => Carbon::now(),
                ]);
            });
        });
    }

    public function getRouteKeyName(): string
    {
        return "slug";
    }

    public function tracks(): MorphToMany
    {
        return $this->morphedByMany(Track::class, "channelable")->withPivot([
            "id",
            "channelable_id",
            "order",
        ]);
    }

    public function albums(): MorphToMany
    {
        return $this->morphedByMany(Album::class, "channelable")->withPivot([
            "id",
            "channelable_id",
            "order",
        ]);
    }

    public function artists(): MorphToMany
    {
        return $this->morphedByMany(Artist::class, "channelable")->withPivot([
            "id",
            "channelable_id",
            "order",
        ]);
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, "channelable")->withPivot([
            "id",
            "channelable_id",
            "order",
        ]);
    }

    public function genres(): MorphToMany
    {
        return $this->morphedByMany(Genre::class, "channelable")->withPivot([
            "id",
            "channelable_id",
            "order",
        ]);
    }

    public function playlists(): MorphToMany
    {
        return $this->morphedByMany(Playlist::class, "channelable")->withPivot([
            "id",
            "channelable_id",
            "order",
        ]);
    }

    public function channels(): MorphToMany
    {
        return $this->morphedByMany(Channel::class, "channelable")->withPivot([
            "id",
            "channelable_id",
            "order",
        ]);
    }

    public function loadContent(
        array $params = [],
        Channel $parent = null
    ): self {
        $channelContent = app(LoadChannelContent::class)->execute(
            $this,
            $params,
            $parent,
        );
        $this->setRelation("content", $channelContent);
        return $this;
    }

    public function getConfigAttribute(): ?array
    {
        return isset($this->attributes["config"])
            ? json_decode($this->attributes["config"], true)
            : [];
    }

    public function setConfigAttribute($value)
    {
        $this->attributes["config"] = is_array($value)
            ? json_encode($value)
            : $value;
    }

    public function toSearchableArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
        ];
    }

    public function filterableFields(): array
    {
        return ["id"];
    }

    public static function getModelTypeAttribute(): string
    {
        return Channel::MODEL_TYPE;
    }
}
