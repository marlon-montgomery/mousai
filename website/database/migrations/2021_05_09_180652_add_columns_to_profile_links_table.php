<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProfileLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profile_links', function (Blueprint $table) {
            $table->string('linkeable_type')->index()->after('user_id');
            if ( ! Schema::hasColumn('profile_links', 'linkeable_id')) {
                $table->renameColumn('user_id', 'linkeable_id');
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
