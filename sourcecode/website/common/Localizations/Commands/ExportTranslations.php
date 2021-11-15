<?php namespace Common\Localizations\Commands;

use App\Services\Admin\GetAnalyticsHeaderData;
use App\User;
use Auth;
use Common\Auth\Permissions\Permission;
use Common\Core\Values\ValueLists;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class ExportTranslations extends Command
{
    /**
     * @var string
     */
    protected $signature = 'translations:export';

    /**
     * @var string
     */
    protected $description = 'Export default laravel translations as flattened json file.';

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs)
    {
        parent::__construct();

        $this->fs = $fs;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $messages = array_merge(
            $this->getCustomValidationMessages(),
            $this->GetDefaultValidationMessages(),
            $this->getDefaultMenuLabels(),
            $this->getAnalyticsHeaderLabels(),
            $this->getPermissionNamesAndDescriptions()
        );

        $this->fs->put(resource_path('server-translations.json'), json_encode($messages));

        $this->info('Translation lines exported as json.');
    }

    private function getAnalyticsHeaderLabels()
    {
        if (class_exists(GetAnalyticsHeaderData::class)) {
            $data = app(GetAnalyticsHeaderData::class)->execute(null);
            return collect($data)
                ->pluck('name')
                ->flatten()
                ->mapWithKeys(function($key) {
                    return [$key => $key];
                })->toArray();
        }

        return [];
    }
    
    private function getDefaultMenuLabels()
    {
        $menus = Arr::first(config('common.default-settings'), function($setting) {
            return $setting['name'] === 'menus';
        });

        if ($menus) {
            return collect(json_decode($menus['value'], true))
                ->pluck('items.*.label')
                ->flatten()
                ->mapWithKeys(function($key) {
                    return [$key => $key];
                })->toArray();
        }

        return [];
    }

    /**
     * Get custom validation messages from Laravel Request files.
     *
     * @return array
     */
    private function getCustomValidationMessages()
    {
        if ( ! $this->fs->exists(app_path('Http/Requests'))) {
            return [];
        }

        $files = $this->fs->files(app_path('Http/Requests'));
        $messages = [];

        foreach ($files as $file) {

            //make namespace from file path
            $namespace = str_replace([base_path() . DIRECTORY_SEPARATOR, '.php'], '', $file);
            $namespace = ucfirst(str_replace('/', '\\', $namespace));

            try {
                //need to use translation as a key (source) and value (translation)
                foreach ((new $namespace)->messages() as $message) {
                    $messages[$message] = $message;
                }
            } catch (\Exception $e) {
                //
            }
        }

        return $messages;
    }

    /**
     * Get default validation messages from laravel translation files.
     *
     * @return array
     */
    private function GetDefaultValidationMessages()
    {
        $paths = $this->fs->files(resource_path('lang/en'));

        $compiled = [];

        foreach ($paths as $path) {
            $lines = $this->fs->getRequire($path);

            foreach ($lines as $key => $line) {
                if ($key === 'custom') continue;

                //flatten multi array translations
                if (is_array($line)) {
                    foreach ($line as $subkey => $subline) {
                        $compiled[$subline] = $subline;
                    }

                    //simply copy regular translation lines
                } else {
                    $compiled[$line] = $line;
                }
            }
        }

        return $compiled;
    }

    private function getPermissionNamesAndDescriptions()
    {
        Auth::login(User::findAdmin());
        $lines = app(ValueLists::class)->permissions()
            ->map(function(Permission $permission) {
                return [
                    $permission['display_name'],
                    $permission['group'],
                    $permission['description'],
                ];
            })->flatten(1)->unique()->toArray();

        return array_combine($lines, $lines);
    }
}
