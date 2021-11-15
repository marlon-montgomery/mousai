<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkspaceInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable('workspace_invites')) {
            Schema::create('workspace_invites', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('avatar', 80)->nullable();
                $table->integer('workspace_id')->unsigned()->index();
                $table->integer('user_id')->unsigned()->index()->nullable();
                $table->string('email', 80)->index();
                $table->integer('role_id')->unsigned()->index();
                $table->timestamps();
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
        Schema::dropIfExists('workspace_invites');
    }
}
