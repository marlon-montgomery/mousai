<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateGenreArtistTableToV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('genre_artist', function (Blueprint $table) {
            if (Schema::hasColumn('genre_artist', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('genre_artist', 'updated_at')) {
                $table->dropColumn('updated_at');
            }

            $table->renameColumn('artist_id', 'genreable_id');
            $table->string('genreable_type', 10)->index()->default('App\\\Artist');
            $table->dropIndex('genre_artist_artist_id_genre_id_unique');

            $table->unique(['genreable_id', 'genreable_type', 'genre_id']);
        });

        Schema::table('genre_artist', function (Blueprint $table) {
            $table->rename('genreables');
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
