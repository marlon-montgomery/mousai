<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkspaceUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable('workspace_user')) {
            Schema::create('workspace_user', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->unsigned()->index();
                $table->integer('workspace_id')->unsigned()->index();
                $table->integer('role_id')->unsigned()->index()->nullable();
                $table->boolean('is_owner')->index()->default(false);
                $table->timestamps();

                $table->unique(['workspace_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workspace_user');
    }
}
