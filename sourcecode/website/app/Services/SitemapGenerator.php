<?php namespace App\Services;

use App;
use App\Album;
use App\Artist;
use App\Channel;
use App\Genre;
use App\Playlist;
use App\Track;
use App\User;
use Common\Admin\Sitemap\BaseSitemapGenerator;

class SitemapGenerator extends BaseSitemapGenerator
{
    protected function getAppQueries(): array
    {
        return [
            app(Artist::class)
                ->where("fully_scraped", true)
                ->orWhereNull("spotify_id")
                ->select(["id", "name", "updated_at"]),
            app(Album::class)
                ->where("fully_scraped", true)
                ->orWhereNull("spotify_id")
                ->with(['artists'])
                ->select(["id", "name", "updated_at"]),
            app(Track::class)->select(["id", "name", "updated_at"]),
            app(Playlist::class)
                ->where("public", true)
                ->select(["id", "name", "updated_at"]),
            app(Genre::class)->select(["id", "name", "updated_at"]),
            app(User::class)->select([
                "id",
                "first_name",
                "last_name",
                "email",
                "updated_at",
            ]),
        ];
    }

    protected function getAppStaticUrls(): array
    {
        return Channel::all()
            ->map(function (Channel $channel) {
                return [
                    "path" => app(UrlGenerator::class)->channel(
                        $channel->toArray(),
                    ),
                    "updated_at" => $channel->updated_at,
                ];
            })
            ->toArray();
    }
}
