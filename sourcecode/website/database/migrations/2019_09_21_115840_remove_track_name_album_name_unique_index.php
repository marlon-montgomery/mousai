<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveTrackNameAlbumNameUniqueIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tracks', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('tracks');

            if (array_key_exists('name_album_unique', $indexesFound)) {
                $table->dropUnique('name_album_unique');
            }

            if (array_key_exists('tracks_name_album_name_unique', $indexesFound)) {
                $table->dropUnique('tracks_name_album_name_unique');
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
