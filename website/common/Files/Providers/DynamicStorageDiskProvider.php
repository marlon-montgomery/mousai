<?php

namespace Common\Files\Providers;

use Arr;
use Config;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Storage;

class DynamicStorageDiskProvider extends ServiceProvider
{
    protected $customCreators = [
        'digitalocean' => DynamicStorageDiskProvider::class,
        'dropbox' => DropboxServiceProvider::class,
        'backblaze' => BackblazeServiceProvider::class,
    ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('dynamic-uploads', function (Application $app, $initialConfig) {
            return $this->resolveDisk('uploads', $initialConfig);
        });

        Storage::extend('dynamic-public', function (Application $app, $initialConfig) {
            return $this->resolveDisk('public', $initialConfig);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * @param string $type
     * @param array $initialConfig
     * @return FilesystemAdapter
     */
    private function resolveDisk($type, $initialConfig)
    {
        $driverName = Config::get("common.site.{$type}_disk_driver", 'local');
        $config = array_merge($initialConfig, Config::get("services.$driverName", []));
        $config['driver'] = $driverName;

        // set root based on drive type and name
        if ($driverName === 'local') {
            $config['root'] = $type === 'public' ? public_path('storage') : storage_path('app/uploads');
        } else {
            $config['root'] = $type === 'public' ? 'storage' : 'uploads';
        }

        // unset "storage" url from remote drives as "$disk->url()" will generate "storage/file_entry.jpg" url
        if ($driverName !== 'local' && Arr::get($config, 'url') === 'storage') {
            unset($config['url']);
        }

        $dynamicConfigKey = "{$type}_{$driverName}";
        Config::set("filesystems.disks.{$dynamicConfigKey}", $config);

        return Storage::disk($dynamicConfigKey);
    }
}
