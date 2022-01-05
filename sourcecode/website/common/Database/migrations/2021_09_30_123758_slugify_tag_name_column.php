<?php

use Common\Tags\Tag;
use Illuminate\Database\Migrations\Migration;

class SlugifyTagNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Tag::withoutSyncingToSearch(function () {
            /** @var Tag $tag */
            foreach (Tag::cursor() as $tag) {
                $slugName = slugify($tag->name);

                if (!$tag->display_name) {
                    $tag->display_name = $tag->name;
                }

                if ($slugName !== $tag->name) {
                    $tag->name = $slugName;
                    Tag::where('name', $slugName)->delete();
                }

                $tag->save();
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
