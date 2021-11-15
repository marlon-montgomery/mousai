<?php

namespace App\Services\Providers\Spotify;

use App\Track;

class SpotifyTrack
{

    /**
     * @var SpotifyHttpClient
     */
    private $httpClient;

    /**
     * @var SpotifyNormalizer
     */
    private $normalizer;

    public function __construct(SpotifyHttpClient $spotifyHttpClient, SpotifyNormalizer $normalizer) {
        $this->httpClient = $spotifyHttpClient;
        $this->normalizer = $normalizer;
    }

    public function getContent(Track $track): ?array
    {
        return $this->httpClient->get("tracks/{$track->spotify_id}");
    }
}
