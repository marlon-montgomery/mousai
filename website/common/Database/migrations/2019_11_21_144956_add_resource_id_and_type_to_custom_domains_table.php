<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResourceIdAndTypeToCustomDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->integer('resource_id')->unsigned()->index()->nullable();
            $table->string('resource_type', 20)->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->dropColumn('resource_id');
            $table->dropColumn('resource_type');
        });
    }
}
