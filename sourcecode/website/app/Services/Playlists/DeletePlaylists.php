<?php

namespace App\Services\Playlists;

use App\Playlist;
use Illuminate\Support\Collection;
use Storage;

class DeletePlaylists
{
    /**
     * @param Playlist[]|Collection $playlists
     */
    public function execute($playlists)
    {
        foreach ($playlists as $playlist) {
            if ($playlist->image) {
                Storage::disk('public')->delete('playlist-images/' . pathinfo($playlist->image, PATHINFO_FILENAME));
            }

            $playlist->tracks()->detach();
            $playlist->delete();
        }
    }
}
