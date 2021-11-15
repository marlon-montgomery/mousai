<?php

namespace Common\Core\Commands;

use File;
use Illuminate\Console\Command;
use Str;

class GenerateChecksums extends Command
{
    /**
     * @var string
     */
    protected $signature = 'checksums:generate';

    public function handle(): int
    {
        $rootPath = base_path();
        $allFiles = File::allFiles($rootPath);
        $bar = $this->output->createProgressBar(count($allFiles));
        $bar->start();

        $checksums = [];
        foreach ($allFiles as $file) {
            if (Str::startsWith($file->getFilename(), '.')) {
                continue;
            }
            $relativePath = str_replace($rootPath, '', $file->getPathname());
            $checksums[$relativePath] = md5_file($file);
            $bar->advance();
        }
        file_put_contents("$rootPath/checksums.json", json_encode($checksums));

        $bar->finish();

        return 0;
    }
}
