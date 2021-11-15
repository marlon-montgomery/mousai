<?php

use App\Album;
use App\Artist;
use App\Services\Providers\SaveOrUpdate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

class MigrateAlbumsToManyToManyArtistRelation extends Migration
{
    use SaveOrUpdate;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('albums', 'artist_type')) return;

        Album::where('artist_type', Artist::class)
            ->where('artist_id', '!=', 0)
            ->chunkById(500, function(Collection $albums) {
                $records = $albums->map(function(Album $album) {
                    return [
                        'artist_id' => $album->artist_id,
                        'album_id' => $album->id,
                        'primary' => true,
                    ];
                });
                $this->saveOrUpdate($records->toArray(), 'artist_album');
                DB::table('albums')->whereIn('id', $albums->pluck('id'))->update(['artist_id' => null]);
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
