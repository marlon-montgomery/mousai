<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('permissionables')) return;

        Schema::create('permissionables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('permission_id')->index();
            $table->integer('permissionable_id')->index();
            $table->string('permissionable_type', 40)->index();
            $table->text('restrictions')->nullable();

            $table->unique(['permission_id', 'permissionable_id', 'permissionable_type'], 'permissionable_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissionables');
    }
}
