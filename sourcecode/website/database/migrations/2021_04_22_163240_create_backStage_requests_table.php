<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackStageRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('backstage_requests')) return;
        Schema::create('backstage_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index()->default('become-artist');
            $table->string('artist_name')->nullable();
            $table->bigInteger('artist_id')->unsigned()->nullable();
            $table->text('data');
            $table->bigInteger('user_id')->unsigned()->index();
            $table->string('status', 20)->default('pending')->index();
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
        Schema::dropIfExists('backstage_requests');
    }
}
