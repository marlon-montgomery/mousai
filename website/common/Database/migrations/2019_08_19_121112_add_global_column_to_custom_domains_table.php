<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGlobalColumnToCustomDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('custom_domains', 'global')) return;
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->boolean('global')->index()->default(0);
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
            $table->dropColumn('global');
        });
    }
}
