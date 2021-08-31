<?php namespace App\Services\Artists;

use App\Album;
use App\Artist;
use App\Genre;
use App\Services\Providers\SaveOrUpdate;
use App\Services\Providers\Spotify\SpotifyTrackSaver;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class ArtistSaver {

    use SaveOrUpdate;

    public function save(array $data): Artist
    {
        $data['mainInfo']['updated_at'] = Carbon::now();
        $this->saveOrUpdate([$data['mainInfo']], 'artists');
        $artist = app(Artist::class)->where('spotify_id', $data['mainInfo']['spotify_id'])->first();

        if (isset($data['albums'])) {
            $this->saveOrUpdate($data['albums'], 'albums');
            $albums = Album::whereIn('spotify_id', $data['albums']->pluck('spotify_id'))->get();
            $artist->albums()->syncWithoutDetaching($albums->pluck('id'));
            $artist->setRelation('albums', $albums);
            app(SpotifyTrackSaver::class)->save($data['albums'], $albums);
        }

        if (isset($data['similar'])) {
            $this->saveSimilar($data['similar'], $artist);
        }

        if (isset($data['genres']) && ! empty($data['genres'])) {
            $this->saveGenres($data['genres'], $artist);
        }

        return $artist;
    }

    /**
     * Save and attach artist genres.
     *
     * @param array $genres
     * @param Artist $artist
     */
    public function saveGenres($genres, $artist) {

        $existing = Genre::whereIn('name', $genres)->get();
        $ids = [];

        foreach($genres as $genre) {
            $dbGenre = $existing->filter(function($item) use($genre) { return $item->name === $genre; })->first();

            //genre doesn't exist in db yet, so we need to insert it
            if ( ! $dbGenre) {
                try {
                    $dbGenre = Genre::create(['name' => $genre]);
                } catch(Exception $e) {
                    continue;
                }
            }

            $ids[] = $dbGenre->id;
        }

        //attach genres to artist
        $artist->genres()->sync($ids, false);
    }

    /**
     * @param Collection $similar
     * @param $artist
     * @return void
     */
    public function saveSimilar($similar, $artist)
    {
        $spotifyIds = $similar->pluck('spotify_id');

        // insert similar artists that don't exist in db yet
        $this->saveOrUpdate($similar, 'artists', true);

        // get ids in database for artist we just inserted
        $ids = Artist::whereIn('spotify_id', $spotifyIds)->pluck('id');

        // attach ids to given artist
        $artist->similar()->sync($ids);
    }
}
