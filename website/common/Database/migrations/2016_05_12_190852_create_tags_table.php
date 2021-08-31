<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tags')) return;

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->index()->unique();
            $table->string('display_name')->nullable();
            $table->string('type', 30)->index()->default('custom');
            $table->timestamp('created_at')->index()->nullable();
            $table->timestamp('updated_at')->index()->nullable();

            $table->collation = config('database.connections.mysql.collation');
            $table->charset = config('database.connections.mysql.charset');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tags');
    }
}
