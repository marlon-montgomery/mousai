<?php

namespace App\Actions\Track;

use App\Track;
use Common\Files\Actions\Deletion\DeleteEntries;

class DeleteTracks
{
    public function execute(array $trackIds)
    {
        $tracks = app(Track::class)->whereIn('id', $trackIds)->get();

        // delete waves
        $wavePaths = array_map(function($id) {
            return "waves/{$id}.json";
        }, $trackIds);
        app(Track::class)->getWaveStorageDisk()->delete($wavePaths);

        // delete image and music files
        $imagePaths = $tracks->pluck('image')->filter();
        $musicPaths = $tracks->pluck('url')->filter();
        app(DeleteEntries::class)->execute([
            'paths' => $imagePaths->concat($musicPaths)->toArray(),
        ]);

        app(Track::class)->destroy($tracks->pluck('id'));
    }
}
