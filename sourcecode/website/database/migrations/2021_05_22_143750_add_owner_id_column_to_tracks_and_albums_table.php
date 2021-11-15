<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOwnerIdColumnToTracksAndAlbumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('tracks', 'owner_id')) {
            Schema::table('tracks', function (Blueprint $table) {
                $table->bigInteger('owner_id')->unsigned()->nullable()->index();
            });
        }

        if ( ! Schema::hasColumn('albums', 'owner_id')) {
            Schema::table('albums', function (Blueprint $table) {
                $table->bigInteger('owner_id')->unsigned()->nullable()->index();
            });
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
