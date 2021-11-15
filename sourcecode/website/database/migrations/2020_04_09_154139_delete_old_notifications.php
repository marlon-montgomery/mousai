<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Notifications\DatabaseNotification;

class DeleteOldNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // delete notifications before this update as their content is not formatted properly
        DatabaseNotification::where('created_at', '<', Carbon::now())->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
