<?php

namespace Common\Csv;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Storage;

/**
 * Common\Csv\CsvExport
 *
 * @property int user_id
 * @property string download_name
 * @property int $id
 * @property string $cache_name
 * @property int $user_id
 * @property string $download_name
 * @property string $uuid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|CsvExport newModelQuery()
 * @method static Builder|CsvExport newQuery()
 * @method static Builder|CsvExport query()
 * @mixin Eloquent
 */
class CsvExport extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    public function storeFile($stream): bool
    {
        Storage::delete($this->filePath());
        return Storage::writeStream($this->filePath(), $stream);
    }

    public function filePath(): string
    {
        return "exports/csv/{$this->uuid}.csv";
    }

    public function downloadLink(): string
    {
        return url("secure/csv/download/$this->id");
    }
}
