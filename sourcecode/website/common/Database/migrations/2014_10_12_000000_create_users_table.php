<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('users')) return;

	    Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('username', 100)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('gender', 20)->nullable();
            $table->text('permissions')->nullable();
            $table->string('email')->unique();
			$table->string('password', 60);
            $table->string('card_brand', 30)->nullable();
            $table->string('card_last_four', 4)->nullable();
			$table->rememberToken();
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
		Schema::drop('users');
	}

}
