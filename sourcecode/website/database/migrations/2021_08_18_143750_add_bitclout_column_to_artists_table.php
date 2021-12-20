<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBitcloutColumnToArtistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('artists','bitclout')){
            Schema::table('artists',function(Blueprint $table){
                $table->string('bitclout')->nullable()->after('name');
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
        if(Schema::hasColumn('artists','bitclout')){
            Schema::table('artists',function(Blueprint $table){
                $table->dropColumn('bitclout');
            });
        }
    }
}


