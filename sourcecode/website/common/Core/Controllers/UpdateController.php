<?php namespace Common\Core\Controllers;

use Cache;
use Common\Core\AppUrl;
use Common\Core\BaseController;
use Common\Database\MigrateAndSeed;
use Common\Settings\DotEnvEditor;
use Exception;
use File;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Schema;

class UpdateController extends BaseController
{
    /**
     * @var DotEnvEditor
     */
    private $dotEnvEditor;

    public function __construct(DotEnvEditor $dotEnvEditor)
    {
        $this->dotEnvEditor = $dotEnvEditor;

        if (
            !config('common.site.disable_update_auth') &&
            version_compare(
                config('common.site.version'),
                $this->getAppVersion(),
            ) === 0
        ) {
            $this->middleware('isAdmin');
        }
    }

    /**
     * @return Factory|View
     */
    public function show()
    {
        $requirements = collect([
            'PDO' => [
                'result' => defined('PDO::ATTR_DRIVER_NAME'),
                'errorMessage' => 'PHP PDO extension is required.',
            ],
            'XML' => [
                'result' => extension_loaded('xml'),
                'errorMessage' => 'PHP XML extension is required.',
            ],
            'Mbstring' => [
                'result' => extension_loaded('mbstring'),
                'errorMessage' => 'PHP mbstring extension is required.',
            ],
            'Fileinfo' => [
                'result' => extension_loaded('fileinfo'),
                'errorMessage' => 'PHP fileinfo extension is required.',
            ],
            'OpenSSL' => [
                'result' => extension_loaded('openssl'),
                'errorMessage' => 'PHP openssl extension is required.',
            ],
            'GD' => [
                'result' => extension_loaded('gd'),
                'errorMessage' => 'PHP GD extension is required.',
            ],
            'fpassthru' => [
                'result' => function_exists('fpassthru'),
                'errorMessage' =>
                    '"fpassthru" PHP function needs to be enabled.',
            ],
            'Curl' => [
                'result' => extension_loaded('curl'),
                'errorMessage' => 'PHP curl functionality needs to be enabled.',
            ],
            'Zip' => [
                'result' => class_exists('ZipArchive'),
                'errorMessage' =>
                    'PHP ZipArchive extension needs to be installed.',
            ],
        ]);

        $directories = [
            '',
            'storage',
            'storage/app',
            'storage/logs',
            'storage/framework',
            'public',
        ];

        $baseDir = base_path();
        foreach ($directories as $directory) {
            $path = rtrim("$baseDir/$directory", '/');
            $writable = is_writable($path);
            if (!$writable) {
                $result = [
                    'path' => $path,
                    'result' => false,
                    'errorMessage' => '',
                ];
                $result['errorMessage'] = is_dir($path)
                    ? 'Make this directory writable by giving it 755 or 777 permissions via file manager.'
                    : 'Make this directory writable by giving it 644 permissions via file manager.';
                $requirements[] = $result;
            }
        }

        return view('common::update.update')->with([
            'htmlBaseUri' => app(AppUrl::class)->htmlBaseUri,
            'requirements' => $requirements,
            'requirementsFailed' => $requirements->some(function ($req) {
                return !$req['result'];
            }),
        ]);
    }

    public function update()
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(0);

        //fix "index is too long" issue on MariaDB and older mysql versions
        Schema::defaultStringLength(191);

        app(MigrateAndSeed::class)->execute();

        if (
            file_exists(base_path('env.example')) &&
            file_exists(base_path('.env'))
        ) {
            $envExampleValues = $this->dotEnvEditor->load('env.example');
            $currentEnvValues = $this->dotEnvEditor->load('.env');
            $envValuesToWrite = array_diff_key(
                $envExampleValues,
                $currentEnvValues,
            );
            $envValuesToWrite['app_version'] = $envExampleValues['app_version'];
            $envValuesToWrite['installed'] = true;

            // mark mail as setup if app was installed before this setting was added.
            if (!isset($currentEnvValues['mail_setup'])) {
                $envValuesToWrite['mail_setup'] = true;
            }

            if (
                isset($currentEnvValues['scout_driver']) &&
                $currentEnvValues['scout_driver'] === 'mysql-like'
            ) {
                $envValuesToWrite['scout_driver'] = 'mysql';
            }

            $this->dotEnvEditor->write($envValuesToWrite);
        }

        Cache::flush();

        // clear cached views
        $path = config('view.compiled');
        foreach (File::glob("{$path}/*") as $view) {
            File::delete($view);
        }

        return view('common::update.update-complete')->with([
            'htmlBaseUri' => app(AppUrl::class)->htmlBaseUri,
        ]);
    }

    private function getAppVersion(): string
    {
        try {
            return $this->dotEnvEditor->load('env.example')['app_version'];
        } catch (Exception $e) {
            return config('common.site.version');
        }
    }
}
