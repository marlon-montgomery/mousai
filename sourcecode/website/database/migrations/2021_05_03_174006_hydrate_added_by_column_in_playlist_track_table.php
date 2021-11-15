<?php

use App\Playlist;
use Illuminate\Database\Migrations\Migration;

class HydrateAddedByColumnInPlaylistTrackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Playlist::cursor() as $playlist) {
            DB::table('playlist_track')->where('playlist_id', $playlist->id)->update(['added_by' => $playlist->owner_id]);
        }
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
