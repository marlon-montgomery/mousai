<?php

namespace Common\Files\Providers;

use Aws\S3\S3Client;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Storage;
use Arr;

class BackblazeServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('backblaze', function ($app, $config) {
            $region = $config['region'];

            $client = new S3Client([
                'credentials' => [
                    'key'    => $config['key_id'],
                    'secret' => $config['application_key']
                ],
                'region' => $region,
                'version' => 'latest',
                'endpoint' => "https://s3.$region.backblazeb2.com",
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
