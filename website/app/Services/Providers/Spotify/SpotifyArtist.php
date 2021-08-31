<?php namespace App\Services\Providers\Spotify;

use App\Artist;
use Arr;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Log;

class SpotifyArtist
{
    /**
     * @var SpotifyHttpClient
     */
    private $httpClient;

    /**
     * @var SpotifyNormalizer
     */
    private $normalizer;

    public function __construct(
        SpotifyHttpClient $spotifyHttpClient,
        SpotifyNormalizer $normalizer
    ) {
        $this->httpClient = $spotifyHttpClient;
        $this->normalizer = $normalizer;
    }

    public function getContent(Artist $artist, array $options = []): ?array
    {
        if ($artist->spotify_id) {
            $spotifyArtist = $this->httpClient->get(
                "artists/{$artist->spotify_id}",
            );
        }

        // if couldn't find artist, bail
        if (!isset($spotifyArtist)) {
            return null;
        }

        $mainInfo = $this->normalizer->artist($spotifyArtist, true);

        // make sure name is the same as we got passed in as sometimes spaces
        // and other things might be in different places on our db and spotify
        if ($artist->name) {
            $mainInfo["name"] = $artist->name;
        }
        $response = [
            "mainInfo" => $mainInfo,
            "genres" => $spotifyArtist["genres"],
        ];

        if (Arr::get($options, "importAlbums", true)) {
            $partialAlbums = $this->httpClient->get(
                "artists/{$artist->spotify_id}/albums?offset=0&limit=50&album_type=album,single",
            );
            $response["albums"] = $this->getFullAlbums($partialAlbums);
        }
        if (Arr::get($options, "importSimilarArtists", true)) {
            $response["similar"] = $this->getSimilar($spotifyArtist["id"]);
        }
        return $response;
    }

    /**
     * @param $artistName
     * @return array|null
     */
    private function findByName($artistName)
    {
        $artist = null;
        $artistSecondary = null;

        try {
            $response = $this->httpClient->get(
                "search?type=artist&q=$artistName&limit=50",
            );
        } catch (BadResponseException $e) {
            Log::error(
                $e
                    ->getResponse()
                    ->getBody()
                    ->getContents(),
                ["query" => "name"],
            );
            $response = [];
        }

        // make sure we get exact name match when searching by name
        if (isset($response["artists"]["items"][0])) {
            foreach ($response["artists"]["items"] as $spotifyArtist) {
                $normalizedSpotifyName = str_replace(
                    [" ", "."],
                    "",
                    strtolower($spotifyArtist["name"]),
                );
                $normalizedSpecifiedName = str_replace(
                    [" ", "."],
                    "",
                    strtolower($artistName),
                );

                if ($normalizedSpotifyName === $normalizedSpecifiedName) {
                    $artist = $spotifyArtist;
                    break;
                }

                if (
                    Str::contains(
                        $normalizedSpotifyName,
                        $normalizedSpecifiedName,
                    )
                ) {
                    $artistSecondary = $spotifyArtist;
                }
            }
        }

        if (!$artist) {
            $artist = $artistSecondary;
        }

        return $artist;
    }

    /**
     * @param string $spotifyId
     * @return Collection
     */
    public function getSimilar($spotifyId)
    {
        $response = $this->httpClient->get(
            "artists/{$spotifyId}/related-artists",
        );

        return collect($response["artists"])->map(function ($artist) {
            return $this->normalizer->artist($artist);
        });
    }

    /**
     * @param array $partialAlbums
     * @return Collection
     */
    public function getFullAlbums($partialAlbums)
    {
        $albums = collect();

        if (empty($partialAlbums["items"])) {
            return $albums;
        }

        // limit to 40 albums per artist max
        // can only fetch 20 albums per spotify request
        $ids = array_slice($this->makeAlbumsIdString($partialAlbums), 0, 2);
        if (!$ids) {
            return $albums;
        }

        // get full album objects from spotify
        foreach ($ids as $key => $idsString) {
            $response = $this->httpClient->get("albums?ids=$idsString");
            if (!isset($response["albums"])) {
                continue;
            }
            $albums = $albums->concat($response["albums"]);
        }

        return $albums->map(function ($spotifyAlbum) {
            return $this->normalizer->album($spotifyAlbum);
        });
    }

    /**
     * Concat ids strings for all albums we want to fetch from spotify.
     *
     * @param mixed $response
     * @return array
     */
    private function makeAlbumsIdString($response)
    {
        $filtered = [];
        $ids = "";

        // filter out deluxe albums and same albums that were released in different markets
        if (isset($response["items"]) && count($response["items"])) {
            foreach ($response["items"] as $album) {
                $name = str_replace(" ", "", strtolower($album["name"]));

                if (Str::contains($name, "(clean")) {
                    continue;
                }

                if (
                    isset($filtered[$name]) &&
                    $filtered[$name]["available_markets"] >=
                        $album["available_markets"]
                ) {
                    continue;
                }

                $filtered[$name] = $album;
            }

            // make multi-dimensional array of 20 spotify album ids as that is the max for albums query
            $chunked = array_chunk(
                array_map(function ($a) {
                    return $a["id"];
                }, $filtered),
                20,
            );

            $ids = array_map(function ($a) {
                return implode(",", $a);
            }, $chunked);
        }

        return $ids;
    }
}
