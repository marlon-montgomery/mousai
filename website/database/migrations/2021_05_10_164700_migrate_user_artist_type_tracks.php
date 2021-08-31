<?php

use App\Track;
use App\User;
use App\UserProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class MigrateUserArtistTypeTracks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('artist_track', 'artist_type')) return;

        DB::table('artist_track')->where('artist_type', User::class)
            ->chunkById(100, function(Collection $rows) {
                $groupedRows = $rows->groupBy('artist_id');
                $userIds = $rows->pluck('artist_id')->unique()->values();
                $users = User::whereIn('id', $userIds)->get();

                $groupedRows->each(function(Collection $group, int $userId) use($users) {
                    $user = $users->get($userId);
                    if ( ! $user) return;

                    $userArtist = $user->getOrCreateArtist();
                    UserProfile::where('user_id', $user->id)->update(['artist_id' => $userArtist->id]);

                    DB::table('artist_track')->where('artist_id', $user->id)->update(['artist_id' => $userArtist->id]);
                });
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
