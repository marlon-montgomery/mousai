<?php

use Illuminate\Database\Migrations\Migration;

class HydrateEmptyOwnerIdColumnInPlaylistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (DB::table('playlist_user')->where('owner', true)->cursor() as $row) {
            DB::table('playlists')->where('id', $row->playlist_id)->update(['owner_id' => $row->user_id]);
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
