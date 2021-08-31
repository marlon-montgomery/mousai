<?php

namespace Common\Files\Commands;

use Common\Files\Actions\Deletion\PermanentlyDeleteEntries;
use Common\Files\FileEntry;
use DB;
use Schema;
use Storage;
use Common\Settings\Settings;
use Illuminate\Console\Command;

class DeleteUploadArtifacts extends Command
{
    protected $map = [
        'branding_media' => [
            'type' => 'settings',
            'keys' => [
                'branding.logo_light',
                'branding.logo_dark'
            ]
        ],
        'homepage_media' => [
            'type' => 'settings',
            'keys' => [
                'homepage.appearance',
            ]
        ],
        'page_media' => [
            'type' => 'model',
            'table' => 'custom_pages',
            'column' => 'body'
        ],

        // mtdb
        'title-videos' => [
            'type' => 'model',
            'table' => 'videos',
            'column' => 'url'
        ],
        'media-images/videos' => [
            'type' => 'model',
            'table' => 'videos',
            'column' => 'thumbnail'
        ],

        // bemusic
        'track_image_media' => [
            'type' => 'model',
            'table' => 'tracks',
            'column' => 'image',
        ],
        'album_media' => [
            'type' => 'model',
            'table' => 'albums',
            'column' => 'image',
        ],
        'track_media' => [
            'type' => 'model',
            'table' => 'tracks',
            'column' => 'url',
        ],
        'artist_media' => [
            'type' => 'model',
            'table' => 'artists',
            'column' => 'image_small',
        ],
        'genre_media' => [
            'type' => 'model',
            'table' => 'genres',
            'column' => 'image',
        ],
        'playlist_media' => [
            'type' => 'model',
            'table' => 'playlists',
            'column' => 'image',
        ],

        // bedesk
        'ticket_images' => [
            'type' => 'model',
            'table' => 'replies',
            'column' => 'body',
        ],
        'article_images' => [
            'type' => 'model',
            'table' => 'articles',
            'column' => 'body',
        ],

        // belink
        'link_overlay_images' => [
            'type' => 'model',
            'table' => 'link_overlays',
            'column' => 'colors',
        ]
    ];

    /**
     * @var string
     */
    protected $signature = 'uploads:clean';

    /**
     * @var string
     */
    protected $description = 'Delete unused files that were uploaded via various application pages.';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $storage = Storage::disk('public');
        $count = 0;
        foreach ($this->map as $folder => $config) {
            if ($storage->exists($folder)) {
                $fileNames = collect($storage->allFiles($folder))
                    ->filter(function($path) use($config) {
                        return $this->shouldDelete($path, $config);
                    })->map(function($path) {
                        return basename($path);
                    });
                $count += $fileNames->count();
                $entryIds = FileEntry::whereIn('file_name', $fileNames)->pluck('id');
                app(PermanentlyDeleteEntries::class)->execute($entryIds);
            }
        }

        $this->info("Deleted $count unused files.");
    }

    /**
     * @param string $path
     * @param array $config
     * @return boolean
     */
    protected function shouldDelete($path, $config)
    {
        if ($config['type'] === 'settings') {
            return collect($config['keys'])->map(function($key) {
                return app(Settings::class)->get($key);
            })->filter(function($configValue) use($path) {
                return \Str::contains($configValue, basename($path));
            })->isEmpty();
        } else if ($config['type'] === 'model') {
            if (Schema::hasTable($config['table'])) {
                $fileName = basename($path);
                return DB::table($config['table'])
                    ->whereNotNull($config['column'])
                    ->where($config['column'], 'like', "%$fileName%")
                    ->count() === 0;
            }
        }
    }
}
