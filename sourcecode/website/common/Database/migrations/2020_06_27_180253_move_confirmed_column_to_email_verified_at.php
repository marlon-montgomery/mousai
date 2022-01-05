<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class MoveConfirmedColumnToEmailVerifiedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('users', 'confirmed')) {
            User::where('confirmed', true)
                ->update(['email_verified_at' => Carbon::now()]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
