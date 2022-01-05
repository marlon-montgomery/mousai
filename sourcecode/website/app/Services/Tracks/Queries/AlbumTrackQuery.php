<?php

namespace App\Services\Tracks\Queries;

use App\Album;
use App\Services\Albums\ShowAlbum;

class AlbumTrackQuery extends BaseTrackQuery
{
    const ORDER_COL = 'number';
    const ORDER_DIR = 'asc';

    public function get($albumId)
    {
        // fetch album tracks from spotify, if not fetched already
        app(ShowAlbum::class)
            ->execute(app(Album::class)->find($albumId), [], true);

        return $this->baseQuery()
            ->where('tracks.album_id', $albumId);
    }
}
