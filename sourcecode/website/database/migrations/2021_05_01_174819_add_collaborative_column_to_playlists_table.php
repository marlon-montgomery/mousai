<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCollaborativeColumnToPlaylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('playlists', 'collaborative')) {
            return;
        }
        Schema::table('playlists', function (Blueprint $table) {
            $table
                ->boolean('collaborative')
                ->default(0)
                ->index();
            $table
                ->bigInteger('plays')
                ->unsigned()
                ->default(0)
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playlists', function (Blueprint $table) {
            $table->dropColumn('collaborative');
        });
    }
}
