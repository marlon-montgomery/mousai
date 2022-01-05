<?php

namespace Common\Settings\Validators;

use Aws\S3\Exception\S3Exception;
use Common\Files\Providers\BackblazeServiceProvider;
use Common\Files\Providers\DigitalOceanServiceProvider;
use Common\Files\Providers\DropboxServiceProvider;
use Config;
use Exception;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Storage;

class StorageCredentialsValidator
{
    const KEYS = [
        'uploads_disk_driver', 'public_disk_driver',

        // dropbox
        'storage_dropbox_access_token', 'storage_dropbox_root',

        // s3
        'storage_s3_key', 'storage_s3_secret',
        'storage_s3_region', 'storage_s3_bucket',

        // ftp
        'storage_ftp_host', 'storage_ftp_username', 'storage_ftp_password',
        'storage_ftp_root', 'storage_ftp_port', 'storage_ftp_passive', 'storage_ftp_ssl',

        // digital ocean
        'storage_digitalocean_key', 'storage_digitalocean_secret',
        'storage_digitalocean_region', 'storage_digitalocean_bucket',

        // rackspace
        'storage_rackspace_username', 'storage_rackspace_key',
        'storage_rackspace_region', 'storage_rackspace_container',

        // backblaze
        'storage_backblaze_key_id', 'storage_backblaze_application_key', 'storage_backblaze_bucket', 'storage_backblaze_region'
    ];

    public function fails($settings)
    {
        $this->setConfigDynamically($settings);
        $this->registerAdapters();

        $messages = array_merge(
            is_null(config('common.site.uploads_disk_driver')) ? [] : $this->validateDisk('uploads'),
            $this->validateDisk('public')
        );

        return empty($messages) ? false : $messages;
    }

    /**
     * @param string $diskName
     * @return array
     */
    private function validateDisk($diskName)
    {
        $driverName = Config::get("common.site.{$diskName}_disk_driver");

        try {
            $disk = Storage::disk($diskName);
            if ($disk->getAdapter() instanceof DropboxAdapter) {
                // dropbox adapter catches all errors silently
                // need to use client directly to check for errors
                $disk->getAdapter()->getClient()->listFolder();
            } else {
                $disk->listContents();
            }
        } catch (Exception $e) {
            if ($driverName === 's3') {
                return $this->getS3Message($e);
            } else {
                $message = $e->getMessage();
                if (\Str::contains($message, 'ftp_chdir(): Failed to change directory')) {
                    $message = 'Could not open "uploads" directory. You might need to create it manually via any FTP manager.';
                }
                return ['storage_group' => "Invalid $driverName credentials.<br>{$message}"];
            }
        }

        return [];
    }

    private function getS3Message(S3Exception $e) {
        return ['storage_group' => "Could not validate credentials. <br> {$e->getAwsErrorMessage()}"];
    }

    private function setConfigDynamically($settings)
    {
        $replacements = ['s3', 'dropbox', 'ftp', 'digitalocean', 'rackspace', 'backblaze'];

        foreach ($settings as $key => $value) {
            if ($key === 'uploads_disk_driver') {
                Config::set('common.site.uploads_disk_driver', $value ?: null);
            } else if ($key === 'public_disk_driver') {
                Config::set('common.site.public_disk_driver', $value ?: null);
            } else {
                // uploads_s3_key => services.s3.key
                $key = str_replace('storage_', '', $key);
                $key = preg_replace('/_/', '.', $key, 1);
                $key = "services.$key";
                foreach ($replacements as $replacement) {
                    $key = str_replace("{$replacement}_", "{$replacement}.", $key);
                }
                Config::set($key, $value ?: null);
            }
        }
    }

    private function registerAdapters()
    {
        app()->register(DigitalOceanServiceProvider::class);
        app()->register(DropboxServiceProvider::class);
        app()->register(BackblazeServiceProvider::class);
    }
}
