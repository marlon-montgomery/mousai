<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePlanAmountToFloat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_plans', function(Blueprint $table) {
            $prefix = DB::getTablePrefix();
            DB::statement("ALTER TABLE {$prefix}billing_plans CHANGE amount amount DECIMAL(13,2) NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->integer('amount')->nullable()->change();
        });
    }
}
