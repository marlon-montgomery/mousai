<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpotifyIdToArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('artists', function (Blueprint $table) {
            if ( ! Schema::hasColumn('artists', 'spotify_id')) {
                $table->char('spotify_id', 22)->unique()->nullable()->index();
            }
            if (Schema::hasColumn('artists', 'bio')) {
                $table->renameColumn('bio', 'bio_legacy');
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
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn('spotify_id');
        });
    }
}
