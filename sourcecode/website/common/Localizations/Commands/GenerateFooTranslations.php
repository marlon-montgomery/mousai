<?php

namespace Common\Localizations\Commands;

use Common\Localizations\LocalizationsRepository;
use Illuminate\Console\Command;

class GenerateFooTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:foo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     */
    public function handle()
    {
        if ($existing = app(LocalizationsRepository::class)->getByNameOrCode('foo')) {
            app(LocalizationsRepository::class)->delete($existing['model']['id']);
        }
        $localization = app(LocalizationsRepository::class)->create([
            'name' => 'Foo',
            'language' => 'foo',
        ]);

        $count = 1;
        foreach ($localization['lines'] as $key => $line) {
            $localization['lines'][$key] = "Foo Bar $count";
            $count++;
        }

        app(LocalizationsRepository::class)->update($localization['model']['id'], ['lines' => $localization['lines']]);

        $this->info('Localization created');
    }
}
