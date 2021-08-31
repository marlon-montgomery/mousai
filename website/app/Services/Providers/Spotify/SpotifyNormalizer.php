<?php

namespace App\Services\Providers\Spotify;

use Carbon\Carbon;
use Arr;

class SpotifyNormalizer
{
    /**
     * @param array $spotifyTrack
     * @param string $albumName
     * @return array
     */
    public function track($spotifyTrack, $albumName = null)
    {
        $track = [
            'duration'   => $spotifyTrack['duration_ms'],
            'name'       => $spotifyTrack['name'],
            'number'     => $spotifyTrack['track_number'],
            'album_name' =>  $albumName ?: Arr::get($spotifyTrack, 'album.name'),
            'artists' => collect(),
            'spotify_id' => $spotifyTrack['id'],
        ];

        if (isset($spotifyTrack['popularity'])) {
            $track['spotify_popularity'] = $spotifyTrack['popularity'];
        }

        if (isset($spotifyTrack['album'])) {
            $track['album'] = $this->album($spotifyTrack['album']);
        }

        foreach ($spotifyTrack['artists'] as $spotifyArtist) {
            $track['artists']->push($this->artist($spotifyArtist));
        }

        return $track;
    }

    /**
     * @param array $spotifyAlbum
     * @param bool $fullyScraped
     * @return array
     */
    public function album($spotifyAlbum, $fullyScraped = false)
    {
        $album = [
            'name' => $spotifyAlbum['name'],
            'image'  => $this->getImage($spotifyAlbum['images'], 1),
            'release_date' => $spotifyAlbum['release_date'],
            'artists' => collect(),
            'spotify_id' => $spotifyAlbum['id'],
        ];

        if (Arr::get($spotifyAlbum, 'tracks')) {
            $tracks = $spotifyAlbum['tracks']['items'] ?? Arr::get($spotifyAlbum, 'tracks', []);
            $album['tracks'] = collect($tracks)->map(function($spotifyTrack) use($album) {
                return $this->track($spotifyTrack, $album['name']);
            });
        }

        if (isset($spotifyAlbum['popularity'])) {
            $album['spotify_popularity'] = $spotifyAlbum['popularity'];
        }

        foreach ($spotifyAlbum['artists'] as $spotifyArtist) {
            $album['artists']->push($this->artist($spotifyArtist));
        }

        if ($fullyScraped) {
            $album['fully_scraped'] = true;
        }

        return $album;
    }

    public function playlist(array $spotifyPlaylist): array
    {
        return [
            'name' => $spotifyPlaylist['name'],
            'description' => $spotifyPlaylist['description'],
            'image' => $this->getImage($spotifyPlaylist['images'], 1),
            'spotify_id' => $spotifyPlaylist['id'],
        ];
    }

    /**
     * @param array $spotifyArtist
     * @param bool $fullyScraped
     * @return array
     */
    public function artist($spotifyArtist, $fullyScraped = false)
    {
        $artist = [
            'name' => $spotifyArtist['name'],
            'spotify_id' => $spotifyArtist['id'],
        ];

        // make sure we don't get too small image as it will be stretched on front end
        if (isset($spotifyArtist['images'])) {
            $images = $spotifyArtist['images'];
            $smallImageIndex = (isset($images[2]) &&
                isset($images[2]['width']) &&
                $images[2]['width'] < 170) ? 1 : 2;
            $artist['image_small'] = $this->getImage($images, $smallImageIndex);
            $artist['image_large'] = $this->getImage($images);
        }

        if (Arr::get($spotifyArtist, 'followers.total') !== null) {
            $artist = array_merge([
                'spotify_followers' => Arr::get($spotifyArtist, 'followers.total') ?: null,
                'spotify_popularity' => Arr::get($spotifyArtist, 'popularity') ?: null,
            ], $artist);
        }

        if ($fullyScraped) {
            $artist['fully_scraped'] = true;
            $artist['updated_at'] = Carbon::now()->toDateTimeString();
        }

        return $artist;
    }

    /**
     * @param mixed $images
     * @param int   $index
     * @return mixed
     */
    private function getImage($images, $index = 0)
    {
        if ($images && count($images)) {

            if (isset($images[$index])) {
                return $images[$index]['url'];
            }

            foreach($images as $image) {
                return $image['url'];
            }
        }

        return null;
    }
}
