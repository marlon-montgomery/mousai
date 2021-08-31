<?php namespace App\Services\Providers\Spotify;

use App\Genre;
use Illuminate\Database\Eloquent\Model;

class SpotifyRadio {

    /**
     * @var SpotifyHttpClient
     */
    private $httpClient;

    /**
     * @var SpotifyTopTracks
     */
    private $spotifyTopTracks;

    public function __construct(SpotifyHttpClient $httpClient, SpotifyTopTracks $spotifyTopTracks) {
        $this->httpClient = $httpClient;
        $this->spotifyTopTracks = $spotifyTopTracks;
    }

    public function getRecommendations(Model $item, string $type)
    {
        if ($item instanceof Genre) {
            $seedId = $item->name;
        } else {
            $seedId = $item->spotify_id ?: $this->getSpotifyId($item, $type);
        }
        
        if ( ! $seedId) {
            return [];
        }

        $response = $this->httpClient->get("recommendations?seed_{$type}s=$seedId&min_popularity=30&limit=100");
        if ( ! isset($response['tracks'])) return [];

        return $this->spotifyTopTracks->saveAndLoad($response['tracks']);
    }

    private function getSpotifyId(Model $item, string $type): ?string
    {
        if ($type === 'artist') {
            $response = $this->httpClient->get("search?q={$item->name}&type=artist&limit=1");
            return $response['artists']['items'][0]['id'] ?? null;
        } else if ($type === 'track') {
            $response = $this->httpClient->get("search?q=artist:{$item->album->artists->first()->name}+{$item->name}&type=track&limit=1");
            return $response['tracks']['items'][0]['id'] ?? null;
        }
    }
}
