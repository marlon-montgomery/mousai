<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCssThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('css_themes')) return;

        Schema::create('css_themes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->boolean('is_dark')->default(0);
            $table->boolean('default_light')->index()->default(0);
            $table->boolean('default_dark')->index()->default(0);
            $table->integer('user_id')->index();
            $table->text('colors');
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
        Schema::dropIfExists('css_themes');
    }
}
