<?php

use App\Artist;
use App\Services\Artists\ArtistSaver;
use App\Services\Providers\SaveOrUpdate;
use App\Track;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;

class MigrateInlineArtistsToPivot extends Migration
{
    use SaveOrUpdate;

    /**
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('tracks', 'artists_legacy')) return;

        app(Track::class)
            ->whereNotNull('artists_legacy')
            ->chunkById(100, function(Collection $tracks) {
                $artistNames = $tracks->pluck('artists_legacy')->map(function($artists) {
                    return explode('*|*', $artists);
                })->flatten()->unique();

                $artists = app(Artist::class)
                    ->whereIn('name', $artistNames)
                    ->select(['id', 'name'])
                    ->get();

                $pivots = $tracks->map(function(Track $track) use($artists) {
                    $artistNames = explode('*|*', $track->artists_legacy);
                    $pivots = array_map(function($artistName) use($artists, $track) {
                        $artist = $artists->first(function(Artist $artist) use($artistName) {
                            return strtolower($artist->name) === strtolower($artistName);
                        });
                        if ($artist) {
                            return [
                                'track_id' => $track->id,
                                'artist_id' => $artist->id
                            ];
                        }
                    }, $artistNames);

                    return $pivots;
                })->flatten(1)->filter();

                if ($pivots->isEmpty()) {
                    return;
                }

                try {
                    $this->saveOrUpdate($pivots->toArray(), 'artist_track');
                    DB::table('tracks')->whereIn('id', $tracks->pluck('id'))->update(['artists_legacy' => null]);
                } catch (Exception $e) {
                    //
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
