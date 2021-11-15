<?php

use App\Genre;
use Illuminate\Database\Migrations\Migration;

class SlugifyGenreNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Genre::withoutSyncingToSearch(function () {
            /** @var Genre $tag */
            foreach (Genre::cursor() as $genre) {
                $slugName = slugify($genre->name);

                if (!$genre->display_name) {
                    $genre->display_name = $genre->name;
                }

                if ($slugName !== $genre->name) {
                    $genre->name = $slugName;
                    Genre::where('name', $slugName)->delete();
                }

                $genre->save();
            }
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
