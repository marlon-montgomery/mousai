<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlbumsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('albums', function(Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('release_date')->nullable()->index();
			$table->string('image')->nullable();
			$table->tinyInteger('spotify_popularity')->nullable()->index();
			$table->bigInteger('owner_id')->unsigned()->nullable()->index();
			$table->boolean('fully_scraped')->default(0);

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
		Schema::drop('albums');
	}

}
