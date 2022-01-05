<?php

namespace App\Services\Albums;

use App\Album;
use App\Genre;
use App\Notifications\ArtistUploadedMedia;
use App\Services\Tracks\CrupdateTrack;
use App\Track;
use App\User;
use Auth;
use Common\Tags\Tag;
use DB;
use Arr;
use Exception;
use Notification;

class CrupdateAlbum
{
    /**
     * @var Album
     */
    private $album;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var CrupdateTrack
     */
    private $createTrack;

    /**
     * @var Track
     */
    private $track;

    /**
     * @var Genre
     */
    private $genre;

    /**
     * @param Album $album
     * @param CrupdateTrack $createTrack
     * @param Tag $tag
     * @param Track $track
     * @param Genre $genre
     */
    public function __construct(Album $album, CrupdateTrack $createTrack, Tag $tag, Track $track, Genre $genre)
    {
        $this->album = $album;
        $this->tag = $tag;
        $this->createTrack = $createTrack;
        $this->track = $track;
        $this->genre = $genre;
    }

    /**
     * @param array $data
     * @param Album|null $initialAlbum
     * @return Album
     */
    public function execute($data, Album $initialAlbum = null)
    {
        $album = $initialAlbum ?? $this->album->newInstance();

        $inlineData = Arr::except($data, ['tracks', 'tags', 'genres']);
        $inlineData['spotify_id'] = $inlineData['spotify_id'] ?? Arr::get($initialAlbum, 'spotify_id');

        if ( ! $initialAlbum) {
            $inlineData['owner_id'] = Auth::id();
        }

        $album->fill($inlineData)->save();

        $newArtists = collect(Arr::get($data, 'artists', []));
        $newArtists = $newArtists->map(function($artistId) {
            if ($artistId === 'CURRENT_USER') {
                return Auth::user()->getOrCreateArtist()->id;
            } else {
                return $artistId;
            }
        });

        // make sure we're only attaching new artists to avoid too many db queries
        if ($album->relationLoaded('artists')) {
            $newArtists = $newArtists->filter(function($newArtistId) use ($album) {
                return !$album->artists()->where('artists.id', $newArtistId)->first();
            });
        }

        if ($newArtists->isNotEmpty()) {
            $pivots = $newArtists->map(function($artistId, $index) use($album) {
                return [
                    'artist_id' => $artistId,
                    'album_id' => $album['id'],
                    'primary' => $index === 0,
                ];
            });

            DB::table('artist_album')->where('album_id', $album->id)->delete();
            DB::table('artist_album')->insert($pivots->toArray());
        }

        $tags = Arr::get($data, 'tags', []);
        $tagIds = $this->tag->insertOrRetrieve($tags)->pluck('id');
        $album->tags()->sync($tagIds);

        $genres = Arr::get($data, 'genres', []);
        $genreIds = $this->genre->insertOrRetrieve($genres)->pluck('id');
        $album->genres()->sync($genreIds);

        $this->saveTracks($data, $album);

        $album->load('tracks', 'artists', 'genres', 'tags');
        $album->tracks->load('artists');

        if ( ! $initialAlbum) {
            $album->artists->first()->followers()->chunkById(1000, function($followers) use($album) {
                try {
                    Notification::send($followers, new ArtistUploadedMedia($album));
                } catch (Exception $e) {
                    //
                }
            });
        }

        return $album;
    }

    private function saveTracks($albumData, Album $album)
    {
        $tracks = collect(Arr::get($albumData, 'tracks', []));
        if ($tracks->isEmpty()) return;

        $trackIds = $tracks->pluck('id')->filter();
        $savedTracks = collect([]);
        if ($trackIds->isNotEmpty()) {
            $savedTracks = $album->tracks()->whereIn('id', $trackIds)->get();
            $savedTracks->load('artists');
        }

        $tracks->each(function($trackData) use($album, $savedTracks) {
            $trackModel = $trackData['id'] ? $savedTracks->find($trackData['id']) : null;
            $this->createTrack->execute(Arr::except($trackData, 'album'), $trackModel, $album, false);
        });
    }
}
