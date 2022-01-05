<?php namespace Common\Settings;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Settings\Setting
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $private
 * @mixin Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 */
class Setting extends Model {

	/**
	 * @var string
	 */
	protected $table = 'settings';

    protected $fillable = ['name', 'value'];

    protected $casts = ['private' => 'integer'];

    /**
     * Cast setting value to int, if it's a boolean number.
     *
     * @param string $value
     * @return int|string
     */
    public function getValueAttribute($value)
    {
        if ($value === 'false') {
            return false;
        }

        if ($value === 'true') {
            return true;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        return $value;
    }

    /**
     * @param $value
     */
    public function setValueAttribute($value)
    {
        if ($value === true) {
            $value = 'true';
        } else if ($value === false) {
            $value = 'false';
        }

        $this->attributes['value'] = (string) $value;
    }
}
