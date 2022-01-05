<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('notification_subscriptions')) return;

        Schema::create('notification_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('notif_id', 5)->index();
            $table->integer('user_id')->index();
            $table->string('channels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_subscriptions');
    }
}
