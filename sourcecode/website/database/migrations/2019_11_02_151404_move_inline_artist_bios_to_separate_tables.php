<?php

use App\Artist;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

class MoveInlineArtistBiosToSeparateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('artists', 'bio_legacy')) return;

        app(Artist::class)
            ->whereNotNull('bio_legacy')
            ->chunkById(100, function(Collection $artists) {
                $bios = $artists->map(function(Artist $artist) {
                    $legacyBio = json_decode($artist['bio_legacy'], true);
                    if ( ! isset($legacyBio['bio']) || ! $legacyBio['bio']) {
                        return null;
                    }
                    return [
                        'content' => trim($legacyBio['bio']),
                        'artist_id' => $artist['id'],
                        'created_at' => $artist->created_at,
                        'updated_at' => $artist->updated_at,
                    ];
                })->filter();

                $bioImages = $artists->map(function(Artist $artist) {
                    $legacyBio = json_decode($artist['bio_legacy'], true);
                    if ( ! isset($legacyBio['images']) ||  ! $legacyBio['images'] || empty($legacyBio['images'])) {
                        return null;
                    }
                    return array_map(function($image) use($artist) {
                        return [
                            'url' => is_string($image) ? $image : $image['url'],
                            'artist_id' => $artist['id'],
                            'created_at' => $artist->created_at,
                            'updated_at' => $artist->updated_at,
                        ];
                    }, $legacyBio['images']);
                })->flatten(1)->filter();

                try {
                    DB::table('artist_bios')->insert($bios->toArray());
                    DB::table('bio_images')->insert($bioImages->toArray());
                } catch (\Exception $e) {
                    //
                }

                app(Artist::class)->whereIn('id', $artists->pluck('id'))->update(['bio_legacy' => null]);
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
