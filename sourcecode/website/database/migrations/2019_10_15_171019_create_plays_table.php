<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('track_plays', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->nullable();
            $table->integer('track_id')->index();
            $table->timestamp('created_at')->nullable();

            $table->string('platform', 30)->nullable()->index();
            $table->string('device', 30)->nullable()->index();
            $table->string('browser', 30)->nullable()->index();
            $table->string('location', 5)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('track_plays');
    }
}
