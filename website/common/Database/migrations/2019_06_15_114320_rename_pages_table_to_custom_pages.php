<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePagesTableToCustomPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable('custom_pages')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->rename('custom_pages');
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
        if ( ! Schema::hasTable('pages')) {
            Schema::table('custom_pages', function (Blueprint $table) {
                $table->rename('pages');
            });
        }
    }
}
