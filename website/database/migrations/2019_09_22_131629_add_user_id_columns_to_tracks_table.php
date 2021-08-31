<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdColumnsToTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tracks', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tracks', 'description')) {
                $table->text('description')->nullable();
            }
            if ( ! Schema::hasColumn('tracks', 'image')) {
                $table->string('image')->nullable();
            }
            $table->string('album_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}
