<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('custom_domains')) return;

        Schema::create('custom_domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host', 100)->index()->unique();
            $table->integer('user_id')->index();
            $table->timestamp('created_at')->index()->nullable();
            $table->timestamp('updated_at')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_domains');
    }
}
