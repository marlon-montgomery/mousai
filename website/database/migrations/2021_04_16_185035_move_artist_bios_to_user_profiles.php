<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveArtistBiosToUserProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('artist_bios')->chunkById(100, function($bios) {
            $values = $bios->toArray();
            $values = array_map(function($bio) {
                $bio = (array) $bio;
                unset($bio['id']);
                $bio['description'] = $bio['content'];
                unset($bio['content']);
                return $bio;
            }, $values);
            DB::table('user_profiles')->insert($values);
        });
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
