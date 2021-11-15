<?php

use App\Artist;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArtistTrackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artist_track', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('artist_id')->unsigned()->index();
            $table->integer('track_id')->unsigned()->index();
            $table->boolean('primary')->default(false)->index();

            $table->unique(['artist_id', 'track_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('artist_track');
    }
}
