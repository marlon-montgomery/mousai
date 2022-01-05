<?php

namespace App\Services\Albums;

use App\Actions\Track\DeleteTracks;
use App\Album;
use App\Track;
use Common\Files\Actions\Deletion\DeleteEntries;
use Illuminate\Support\Collection;

class DeleteAlbums
{
    /**
     * @param array[]|Collection $albumIds
     */
    public function execute($albumIds)
    {
        $albums =  app(Album::class)->whereIn('id', $albumIds)->get();
        app(DeleteEntries::class)->execute([
            'paths' => $albums->pluck('image')->filter()->toArray(),
        ]);
        app(Album::class)->destroy($albums->pluck('id'));

        $trackIds = app(Track::class)->whereIn('album_id', $albumIds)->pluck('id');
        app(DeleteTracks::class)->execute($trackIds->toArray());
    }
}
