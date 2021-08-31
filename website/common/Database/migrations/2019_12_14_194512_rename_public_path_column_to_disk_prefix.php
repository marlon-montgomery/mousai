<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePublicPathColumnToDiskPrefix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_entries', function (Blueprint $table) {
            $prefix = DB::getTablePrefix();
            DB::statement("ALTER TABLE {$prefix}file_entries CHANGE public_path disk_prefix VARCHAR(191) NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_entries', function (Blueprint $table) {
            //
        });
    }
}
