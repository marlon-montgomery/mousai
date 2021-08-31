<?php namespace Common\Core\Controllers;

use Cache;
use Common\Core\AppUrl;
use Common\Core\BaseController;
use Common\Database\MigrateAndSeed;
use Common\Settings\DotEnvEditor;
use Exception;
use File;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
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
        return view('common::update')->with(
            'htmlBaseUri',
            app(AppUrl::class)->htmlBaseUri,
        );
    }

    /**
     * @return RedirectResponse
     */
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

        return redirect('/')->with('status', 'Updated the site successfully.');
    }

    /**
     * @return string
     */
    private function getAppVersion()
    {
        try {
            return $this->dotEnvEditor->load('env.example')['app_version'];
        } catch (Exception $e) {
            return config('common.site.version');
        }
    }
}
