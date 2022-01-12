<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('csv_exports')) return;
        Schema::create('csv_exports', function (Blueprint $table) {
            $table->id();
            $table->string('cache_name', 50)->unique()->index()->nullable();
            $table->integer('user_id')->nullable()->index();
            $table->string('download_name', 50);
            $table->uuid('uuid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('csv_exports');
    }
}
