<?php

namespace App\Services\Providers\Spotify;

use App\Artist;
use App\Services\Providers\SaveOrUpdate;
use App\Track;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SpotifyTrackSaver
{
    use SaveOrUpdate;

    /**
     * @param Collection $spotifyAlbums
     * @param Collection $savedAlbums
     */
    public function save($spotifyAlbums, $savedAlbums)
    {
        $spotifyTracks = $spotifyAlbums->map(function($spotifyAlbum) use($savedAlbums) {
            try {
                $albumId = $savedAlbums->where('spotify_id', $spotifyAlbum['spotify_id'])->first()->id;
            } catch (\Exception $e) {
                dd($spotifyAlbum, $savedAlbums->toArray());
            }
            return $spotifyAlbum['tracks']->map(function($albumTrack) use($albumId) {
                $albumTrack['album_id'] = $albumId;
                $albumTrack['updated_at'] = Carbon::now();
                $albumTrack['created_at'] = Carbon::now();
                return $albumTrack;
            });
        })->flatten(1);

        $this->saveOrUpdate($spotifyTracks, 'tracks');

        // attach artists to tracks
        $artists = collect($spotifyTracks)->pluck('artists')->flatten(1)->unique('spotify_id');

        $this->saveOrUpdate($artists, 'artists');
        $savedArtists = app(Artist::class)->whereIn('spotify_id', $artists->pluck('spotify_id'))->get(['spotify_id', 'id', 'name'])->keyBy('spotify_id');
        $savedTracks = app(Track::class)->whereIn('spotify_id', $spotifyTracks->pluck('spotify_id'))->get(['name', 'album_name', 'spotify_id', 'id'])->keyBy('spotify_id');

        $pivots = collect($spotifyTracks)->map(function($normalizedTrack) use($savedArtists, $savedTracks) {
            return $normalizedTrack['artists']->map(function($normalizedArtist) use($normalizedTrack, $savedArtists, $savedTracks) {
                $savedTrack = $savedTracks[$normalizedTrack['spotify_id']];
                $savedArtist = $savedArtists[$normalizedArtist['spotify_id']];
                if ( ! $savedTrack) {
                    $savedTrack = $savedTracks->first(function(Track $track) use($normalizedTrack) {
                        return $track->name === $normalizedTrack['name'] && $track->album_name === $normalizedTrack['album_name'];
                    });
                }
                if ( ! $savedArtist) {
                    $savedArtist = $savedArtists->first(function(Artist $artist) use($normalizedArtist) {
                        return $artist->name === $normalizedArtist['name'];
                    });
                }
                return [
                    'track_id' => $savedTrack->id,
                    'artist_id' => $savedArtist->id,
                ];
            });
        })->flatten(1);

        $this->saveOrUpdate($pivots, 'artist_track');
    }
}
