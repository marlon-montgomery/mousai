<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTracksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tracks', function(Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('album_name');
            $table->integer('album_id')->unsigned()->index()->nullable();
			$table->tinyInteger('number')->unsigned()->index();
			$table->integer('duration')->unsigned()->nullable();
			$table->string('youtube_id')->index()->nullable();
			$table->tinyInteger('spotify_popularity')->unsigned()->nullable()->index();
            $table->bigInteger('owner_id')->unsigned()->nullable()->index();

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
		Schema::drop('tracks');
	}

}
