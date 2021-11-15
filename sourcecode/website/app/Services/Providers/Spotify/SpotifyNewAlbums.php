<?php namespace App\Services\Providers\Spotify;

use App\Album;
use App\Artist;
use App\Services\Artists\ArtistSaver;
use App\Services\HttpClient;
use App\Services\Providers\ContentProvider;
use App\Services\Providers\SaveOrUpdate;

class SpotifyNewAlbums implements ContentProvider {

    use SaveOrUpdate;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var SpotifyArtist
     */
    private $spotifyArtist;

    /**
     * @var ArtistSaver
     */
    private $saver;

    /**
     * @param ArtistSaver $saver
     * @param SpotifyArtist $spotifyArtist
     * @param SpotifyHttpClient $httpClient
     */
    public function __construct(SpotifyArtist $spotifyArtist, ArtistSaver $saver, SpotifyHttpClient $httpClient)
    {
        $this->saver = $saver;
        $this->httpClient = $httpClient;
        $this->spotifyArtist = $spotifyArtist;

        @ini_set('max_execution_time', 0);
    }

    public function getContent()
    {
        $response = $this->httpClient->get('browse/new-releases?country=US&limit=40');
        $spotifyAlbums = $this->spotifyArtist->getFullAlbums($response['albums']);

        $this->saveOrUpdate($spotifyAlbums, 'albums');
        $savedAlbums = app(Album::class)
            ->whereIn('spotify_id', $spotifyAlbums->pluck('spotify_id'))
            ->orderBy('release_date', 'desc')
            ->limit(40)
            ->get()
            ->keyBy('spotify_id');

        // attach artists to albums
        $spotifyArtists = $spotifyAlbums->pluck('artists')->flatten(1)->unique('spotify_id');
        $this->saveOrUpdate($spotifyArtists, 'artists');
        $savedArtists = app(Artist::class)->whereIn('spotify_id', $spotifyArtists->pluck('spotify_id'))->get(['spotify_id', 'id', 'name'])->keyBy('spotify_id');

        $pivots = collect($spotifyAlbums)->map(function($normalizedAlbum) use($savedArtists, $savedAlbums) {
            return $normalizedAlbum['artists']->map(function($normalizedArtist) use($normalizedAlbum, $savedArtists, $savedAlbums) {
                $savedAlbum = $savedAlbums[$normalizedAlbum['spotify_id']];
                $savedArtist = $savedArtists[$normalizedArtist['spotify_id']];
                if ( ! $savedAlbum) {
                    $savedAlbum = $savedAlbums->first(function(Album $album) use($normalizedAlbum) {
                        return $album->name === $normalizedAlbum['name'];
                    });
                }
                if ( ! $savedArtist) {
                    $savedArtist = $savedArtists->first(function(Artist $artist) use($normalizedArtist) {
                        return $artist->name === $normalizedArtist['name'];
                    });
                }
                return [
                    'album_id' => $savedAlbum->id,
                    'artist_id' => $savedArtist->id,
                ];
            });
        })->flatten(1);
        $this->saveOrUpdate($pivots, 'artist_album');

        app(SpotifyTrackSaver::class)->save($spotifyAlbums, $savedAlbums);

        return $savedAlbums
            ->load('artists', 'tracks')
            ->sortByDesc('artist.spotify_popularity')
            ->values();
    }
}
