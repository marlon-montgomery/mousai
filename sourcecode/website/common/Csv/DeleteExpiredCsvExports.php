<?php

namespace Common\Csv;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Storage;

class DeleteExpiredCsvExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csvExports:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleted csv exports that are expired.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CsvExport::where(
            'created_at',
            '<',
            Carbon::now()->addDays(-1),
        )->chunkById(10, function (Collection $chunk) {
            CsvExport::whereIn('id', $chunk->pluck('id'))->delete();
            $filePaths = $chunk->map(function(CsvExport $export) {
                return $export->filePath();
            });
            Storage::delete($filePaths);
        });

        return 0;
    }
}
