<?php

namespace App\Services\Providers\LocalAndSpotify;

use App\Album;
use App\Artist;
use App\Services\Providers\Local\LocalSearch;
use App\Services\Providers\Spotify\SpotifySearch;
use App\Track;

class LocalAndSpotifySearch extends SpotifySearch
{
    public function search(string $q, int $limit, $modelTypes): array
    {
        $spotifyResults = parent::search($q, $limit, $modelTypes);

        // spotify provider will only search artist, album and track and will use
        //  local provider for the rest of types, so there's no need to double search
        $localModelTypes = array_intersect($modelTypes, [Artist::MODEL_TYPE, Album::MODEL_TYPE, Track::MODEL_TYPE]);
        if ( ! empty($localModelTypes)) {
            $localResults = app(LocalSearch::class)->search($q, $limit, $localModelTypes);
        }

        foreach ($spotifyResults as $type => $results) {
            if (isset($localResults[$type])) {
                $spotifyResults[$type] = $results->merge($localResults[$type])->unique('id')->take($limit);
            }
        }

        return $spotifyResults;
    }
}
