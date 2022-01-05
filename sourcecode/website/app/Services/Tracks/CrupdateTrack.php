<?php

namespace App\Services\Tracks;

use App\Album;
use App\Artist;
use App\Genre;
use App\Notifications\ArtistUploadedMedia;
use App\Services\Providers\SaveOrUpdate;
use App\Track;
use App\User;
use Arr;
use Auth;
use Common\Tags\Tag;
use DB;
use Exception;
use Notification;

class CrupdateTrack
{
    use SaveOrUpdate;

    /**
     * @var Track
     */
    private $track;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Genre
     */
    private $genre;

    public function __construct(Track $track, Tag $tag, Genre $genre)
    {
        $this->track = $track;
        $this->tag = $tag;
        $this->genre = $genre;
    }

    /**
     * @param array $data
     * @param Track|null $initialTrack
     * @param Album|array|null $album
     * @param bool $loadRelations
     * @return Track
     */
    public function execute($data, Track $initialTrack = null, $album = null, $loadRelations = true)
    {
        $track = $initialTrack ?: $this->track->newInstance();

        $inlineData = Arr::except($data, ['artists', 'tags', 'genres', 'album', 'waveData', 'lyrics']);
        $inlineData['spotify_id'] = $inlineData['spotify_id'] ?? Arr::get($initialTrack, 'spotify_id');

        if ( ! $initialTrack) {
            $inlineData['owner_id'] = Auth::id();
        }

        if ($album) {
            $inlineData['album_name'] = $album['name'];
            $inlineData['album_id'] = $album['id'];
        }

        $track->fill($inlineData)->save();

        $newArtists = collect($this->getArtists($data, $album) ?: []);
        $newArtists = $newArtists->map(function($artistId) {
            if ($artistId === 'CURRENT_USER') {
                return Auth::user()->getOrCreateArtist()->id;
            } else {
                return $artistId;
            }
        });

        // make sure we're only attaching new artists to avoid too many db queries
        if ($track->relationLoaded('artists')) {
            $newArtists = $newArtists->filter(function($newArtistId) use ($track) {
                return !$track->artists()->where('artists.id', $newArtistId)->first();
            });
        }

        if ($newArtists->isNotEmpty()) {
            $pivots = $newArtists->map(function($artistId, $index) use($track) {
                return [
                    'artist_id' => $artistId,
                    'track_id' => $track['id'],
                    'primary' => $index === 0,
                ];
            });

            DB::table('artist_track')->where('track_id', $track->id)->delete();
            DB::table('artist_track')->insert($pivots->toArray());
        }

        $tags = Arr::get($data, 'tags', []);
        $tagIds = $this->tag->insertOrRetrieve($tags)->pluck('id');
        $track->tags()->sync($tagIds);

        $genres = Arr::get($data, 'genres', []);
        $genreIds = $this->genre->insertOrRetrieve($genres)->pluck('id');
        $track->genres()->sync($genreIds);

        if ($loadRelations) {
            $track->load('artists', 'tags', 'genres');
        }

        if ( ! $initialTrack && ! $album) {
            $track->artists->first()->followers()->chunkById(1000, function($followers) use($album) {
                try {
                    Notification::send($followers, new ArtistUploadedMedia($album));
                } catch (Exception $e) {
                    //
                }
            });
        }

        if ($waveData = Arr::get($data, 'waveData')) {
            $this->track->getWaveStorageDisk()->put("waves/{$track->id}.json", json_encode($waveData));
        }

        if ($lyrics = Arr::get($data, 'lyrics')) {
            $track->lyric()->create(['text' => $lyrics]);
        }

        return $track;
    }

    /**
     * @param array $trackData
     * @param Album|array|null $album
     * @return array|void
     */
    private function getArtists($trackData, $album = null)
    {
        if ($trackArtists = Arr::get($trackData, 'artists')) {
            return $trackArtists;
        } else if (isset($album['artists'])) {
            return $album['artists'];
        }
    }
}
