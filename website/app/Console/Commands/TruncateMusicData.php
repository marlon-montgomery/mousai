<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Schema;
use Storage;

class TruncateMusicData extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'music:truncate {--force : Force the operation to run when in production.}';

    /**
     * @var string
     */
    protected $description = 'Truncate all music data on the site.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ( ! $this->confirmToProceed()) {
            return;
        }

        $tableNames = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        foreach ($tableNames as $name) {
            if ($name == 'migrations') {
                continue;
            }
            DB::table($name)->truncate();
        }

        Storage::deleteDirectory('waves');
    }
}
