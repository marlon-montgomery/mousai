<?php

namespace Database\Seeders;

use App\Actions\Channel\UpdateChannelContent;
use App\Album;
use App\Artist;
use App\Channel;
use App\Genre;
use App\Track;
use Common\Settings\Settings;
use DB;
use Illuminate\Database\Seeder;

class DefaultChannelsSeeder extends Seeder
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return void
     */
    public function run()
    {
        if ($this->channel->count() === 0) {
            $popularAlbums = $this->channel->create([
                'name' => 'Popular Albums',
                'slug' => 'popular-albums',
                'user_id' => 1,
                'config' => [
                    'contentType' => 'listAll',
                    'contentModel' => Album::MODEL_TYPE,
                    'contentOrder' => 'popularity:desc',
                    'layout' => 'grid',
                    'carouselWhenNested' => true,
                    'seoTitle' => 'Popular Albums',
                    'seoDescription' => 'Most popular albums from hottest artists today.',
                ]
            ]);

            $newReleases = $this->channel->create([
                'name' => 'New Releases',
                'slug' => 'new-releases',
                'user_id' => 1,
                'config' => [
                    'contentType' => 'listAll',
                    'contentModel' => Album::MODEL_TYPE,
                    'contentOrder' => 'created_at:desc',
                    'layout' => 'grid',
                    'carouselWhenNested' => true,
                    'seoTitle' => 'Latest Releases',
                    'seoDescription' => 'Browse and listen to newest releases from popular artists.',
                ]
            ]);

            $genres = $this->channel->create([
                'name' => 'Genres',
                'slug' => 'genres',
                'user_id' => 1,
                'config' => [
                    'contentType' => 'listAll',
                    'contentModel' => Genre::MODEL_TYPE,
                    'contentOrder' => 'popularity:desc',
                    'layout' => 'grid',
                    'seoTitle' => 'Popular Genres',
                    'seo_description' => 'Browse popular genres to discover new music.',
                ]
            ]);

            $tracks = $this->channel->create([
                'name' => 'Popular Tracks',
                'slug' => 'popular-tracks',
                'user_id' => 1,
                'config' => [
                    'contentType' => 'listAll',
                    'contentModel' => Track::MODEL_TYPE,
                    'contentOrder' => 'popularity:desc',
                    'layout' => 'trackTable',
                    'seoTitle' => 'Popular Tracks',
                    'seoDescription' => 'Global Top 50 chart of most popular songs.',
                ]
            ]);

            $discover = $this->channel->create([
                'name' => 'Discover',
                'slug' => 'discover',
                'user_id' => 1,
                'config' => [
                    'contentType' => 'manual',
                    'contentModel' => Channel::MODEL_TYPE,
                    'hideTitle' => true,
                    'contentOrder' => 'channelables.order|asc',
                    'layout' => 'grid',
                    'seoTitle' => "{{site_name}} - Listen to music for free",
                    'seoDescription' => "Find and listen to millions of songs, albums and artists, all completely free on {{SITE_NAME}}.",
                ]
            ]);

            DB::table('channelables')->insert([
                ['channel_id' => $discover->id, 'channelable_type' => Channel::class, 'channelable_id' => $popularAlbums->id, 'order' => 1],
                ['channel_id' => $discover->id, 'channelable_type' => Channel::class, 'channelable_id' => $tracks->id, 'order' => 2],
                ['channel_id' => $discover->id, 'channelable_type' => Channel::class, 'channelable_id' => $newReleases->id, 'order' => 3],
                ['channel_id' => $discover->id, 'channelable_type' => Channel::class, 'channelable_id' => $genres->id, 'order' => 4],
            ]);

            app(Settings::class)->save([
                'homepage.type' => 'Channels',
                'homepage.value' => $discover->id,
            ]);

            collect([$newReleases, $tracks, $genres, $popularAlbums])->each(function(Channel $channel) {
                app(UpdateChannelContent::class)->execute($channel);
            });
        }

        if ( ! Channel::where('slug', 'genre')->first()) {
            $genreChannel = $this->channel->create([
                'name' => '{{channel.genre.display_name}} Artists',
                'slug' => 'genre',
                'user_id' => 1,
                'config' => [
                    'connectToGenreViaUrl' => true,
                    'lockSlug' => true,
                    'preventDeletion' => true,
                    'contentType' => 'listAll',
                    'contentModel' => Artist::MODEL_TYPE,
                    'contentOrder' => 'popularity:desc',
                    'layout' => 'grid',
                    'seoTitle' => '{{channel.genre.display_name}} - {{site_name}}',
                    'seoDescription' => 'Popular {{channel.genre.display_name}} artists.',
                ]
            ]);
        }
    }
}
