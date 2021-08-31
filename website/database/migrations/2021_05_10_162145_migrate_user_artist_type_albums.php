<?php

use App\Album;
use App\User;
use App\UserProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

class MigrateUserArtistTypeAlbums extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('albums', 'artist_type')) return;

        Album::where('artist_type', User::class)
            ->chunkById(100, function(Collection $albums) {
                $groupedAlbums = $albums->groupBy('artist_id');
                $userIds = $albums->pluck('artist_id')->unique()->values();
                $users = User::whereIn('id', $userIds)->get();

                $groupedAlbums->each(function(Collection $albums, int $userId) use($users) {
                    $user = $users->get($userId);
                    if ( ! $user) return;

                    $userArtist = $user->getOrCreateArtist();
                    UserProfile::where('user_id', $user->id)->update(['artist_id' => $userArtist->id]);

                    $records = $albums->map(function(Album $album) use ($userArtist) {
                        return [
                            'artist_id' => $userArtist->id,
                            'album_id' => $album->id,
                            'primary' => true,
                        ];
                    });
                    DB::table('artist_album')->insert($records->toArray());
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
