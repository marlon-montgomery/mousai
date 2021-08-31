<?php

namespace App\Services\Providers\Spotify;

use App\Artist;
use App\Genre;
use App\Services\Providers\ContentProvider;
use App\Services\Providers\SaveOrUpdate;

class SpotifyGenreArtists implements ContentProvider
{
    use SaveOrUpdate;

    /**
     * @var SpotifyHttpClient
     */
    private $client;

    /**
     * @var SpotifyNormalizer
     */
    private $spotifyNormalizer;

    public function __construct(SpotifyHttpClient $client, SpotifyNormalizer $spotifyNormalizer)
    {
        $this->client = $client;
        $this->spotifyNormalizer = $spotifyNormalizer;
    }

    public function getContent(Genre $genre = null)
    {
        $genreName = slugify($genre->name);
        $response = $this->client->get("recommendations?seed_genres=$genreName&target_popularity=100&limit=100");

        $ids = collect($response['tracks'])
            ->pluck('artists')
            ->flatten(1)
            ->sortByDesc('popularity')
            ->slice(0, 50)
            ->pluck('id')
            ->unique()
            ->implode(',');

        if ( ! $ids) {
            return [];
        }

        $response = $this->client->get("artists?ids=$ids");
        $artists = collect($response['artists'])->map(function($spotifyArtist) {
            return $this->spotifyNormalizer->artist($spotifyArtist);
        });

        $this->saveOrUpdate($artists, 'artists');
        $artists = Artist::whereIn('spotify_id', $artists->pluck('spotify_id'))
            ->orderByPopularity('desc')
            ->get();

        $genre->artists()->syncWithoutDetaching($artists->pluck('id')->toArray());

        return $artists;
    }
}
