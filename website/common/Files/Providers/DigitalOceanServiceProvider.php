<?php

namespace Common\Files\Providers;

use Aws\S3\S3Client;
use Illuminate\Support\Arr;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Storage;
use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class DigitalOceanServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('digitalocean', function ($app, $config) {
            $region = $config['region'];

            $client = new S3Client([
                'credentials' => [
                    'key'    => $config['key'],
                    'secret' => $config['secret']
                ],
                'region' => $region,
                'version' => 'latest',
                'endpoint' => "https://$region.digitaloceanspaces.com",
            ]);

            $root = isset($config['root']) ? $config['root'] : null;

            $options = isset($config['options']) ? $config['options'] : [];

            $adapter = new AwsS3Adapter($client, $config['bucket'], $root, $options);

            $flysystemConfig = Arr::only($config, ['visibility', 'disable_asserts', 'url']);
            return new Filesystem($adapter, count($flysystemConfig) > 0 ? $flysystemConfig : null);
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
}
