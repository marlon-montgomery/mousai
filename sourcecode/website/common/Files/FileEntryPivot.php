<?php

namespace Common\Files;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * Common\Files\FileEntryPivot
 *
 * @property int $id
 * @property int $file_entry_id
 * @property int $model_id
 * @property string $model_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $owner
 * @property array $permissions
 * @method static \Illuminate\Database\Eloquent\Builder|FileEntryPivot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileEntryPivot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileEntryPivot query()
 * @mixin \Eloquent
 */
class FileEntryPivot extends MorphPivot
{
    protected $table = 'file_entry_models';

    protected $casts = ['owner' => 'boolean'];

    /**
     * @param $value
     * @return array
     */
    public function getPermissionsAttribute($value)
    {
        if ( ! $value) return [];

        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }
}
