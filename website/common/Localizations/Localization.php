<?php namespace Common\Localizations;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Localizations\Localization
 *
 * @property int $id
 * @property string $name
 * @property string $language
 * @property string $lines
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @mixin Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|Localization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Localization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Localization query()
 */
class Localization extends Model
{
    protected $guarded = ['id'];

    /**
     * @param string $text
     * @return array
     */
    public function getLinesAttribute($text) {
        if ( ! $text) return [];

        return json_decode($text, true);
    }

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = slugify($name);
    }
}
