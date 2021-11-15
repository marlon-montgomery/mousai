<?php namespace App\Services\Providers\Spotify;

use App;
use App\Album;
use App\Artist;
use App\Services\Providers\Local\LocalSearch;
use App\Services\Search\SearchInterface;
use App\Services\Search\SearchSaver;
use App\Track;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Log;

class SpotifySearch extends LocalSearch implements SearchInterface {

    /**
     * @var SpotifyHttpClient
     */
    private $httpClient;
    /**
     * @var SpotifyNormalizer
     */
    private $normalizer;
    /**
     * @var array
     */
    private $spotifyResponse;

    public function __construct(SpotifyHttpClient $spotifyHttpClient, SpotifyNormalizer $normalizer) {
        $this->httpClient = $spotifyHttpClient;
        $this->normalizer = $normalizer;
    }

    public function search(string $q, int $limit, array $modelTypes): array
    {
        $this->query = urldecode($q);
        $this->limit = $limit ?: 10;

        $spotifyTypes = collect($modelTypes)->filter(function($type) {
            return in_array($type, ['artist', 'album', 'track']);
        });

        // if searching only local model types, there's no need to call spotify API
        if ($spotifyTypes->isNotEmpty()) {
            try {
                $typeString = $spotifyTypes->implode(',');
                $response = $this->httpClient->get("search?q=$q&type=$typeString&limit=$limit");
                $this->spotifyResponse = $this->formatResponse($response);
                $this->spotifyResponse = app(SearchSaver::class)->save($this->spotifyResponse);
            } catch(RequestException $e) {
                if ($e->getResponse()) {
                    Log::error($e->getResponse()->getBody()->getContents(), ['query' => $q]);
                }
            }
        }

        return parent::search($q, $limit, $modelTypes);
    }

    private function formatResponse(array $response): array
    {
        $artists = collect(Arr::get($response, 'artists.items', []))->map(function($spotifyArtist) {
            return $this->normalizer->artist($spotifyArtist);
        });
        $albums = collect(Arr::get($response, 'albums.items', []))->map(function($spotifyAlbum) {
            return $this->normalizer->album($spotifyAlbum);
        });
        $tracks = collect(Arr::get($response, 'tracks.items', []))->map(function($spotifyTrack) {
            return $this->normalizer->track($spotifyTrack);
        });
        return ['albums' => $albums, 'tracks' => $tracks, 'artists' => $artists];
    }

    public function artists(): Collection
    {
        return $this->spotifyResponse['artists'] ?? parent::artists();
    }

    public function albums(): Collection
    {
        return $this->spotifyResponse['albums'] ?? parent::albums();
    }

    public function tracks(): Collection
    {
        return $this->spotifyResponse['tracks'] ?? parent::tracks();
    }
}
