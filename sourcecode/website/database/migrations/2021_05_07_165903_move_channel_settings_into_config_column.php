<?php

use App\Album;
use App\Artist;
use App\Channel;
use App\Genre;
use App\Playlist;
use App\Track;
use Illuminate\Database\Migrations\Migration;

class MoveChannelSettingsIntoConfigColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Channel::orderBy('id')->cursor() as $channel) {
            $oldLayout = $channel->layout;
            $config = [
                'layout' => $oldLayout === 'carousel' ? 'grid' : $oldLayout,
                'carouselWhenNested' => $oldLayout === 'carousel'
            ];
            $config['contentModel'] = $channel->content_type !== 'mixed' ?
                $this->oldModelTypeToNew($channel->content_type) :
                null;
            if ($channel->auto_update) {
                if ($channel->auto_update === 'local:track:top') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Track::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                } else if ($channel->auto_update === 'local:album:top') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Album::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                } else if ($channel->auto_update === 'local:genre:top') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Genre::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                } else if ($channel->auto_update === 'local:album:new') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Album::MODEL_TYPE;
                    $config['contentOrder'] = 'created_at:desc';
                } else if ($channel->auto_update === 'local:track:new') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Track::MODEL_TYPE;
                    $config['contentOrder'] = 'created_at:desc';
                } else if ($channel->auto_update === 'local:playlist:top') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Playlist::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                } else if ($channel->auto_update === 'local:artist:top') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Artist::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                } else if ($channel->auto_update === 'spotify:track:top') {
                    $config['contentType'] = 'autoUpdate';
                    $config['contentModel'] = Track::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                    $config['autoUpdateMethod'] = 'spotifyTopTracks';
                } else if ($channel->auto_update === 'spotify:album:top') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Album::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                }  else if ($channel->auto_update === 'spotify:album:new') {
                    $config['contentType'] = 'autoUpdate';
                    $config['contentModel'] = Album::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                    $config['autoUpdateMethod'] = 'spotifyNewAlbums';
                } else if ($channel->auto_update === 'spotify:artist:top') {
                    $config['contentType'] = 'listAll';
                    $config['contentModel'] = Artist::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                } else if ($channel->auto_update === 'lastfm:genre:top') {
                    $config['contentType'] = 'autoUpdate';
                    $config['contentModel'] = Genre::MODEL_TYPE;
                    $config['contentOrder'] = 'popularity:desc';
                    $config['autoUpdateMethod'] = 'lastfmTopGenres';
                }
            } else {
                $config['contentType'] = 'manual';
                $config['contentOrder'] = 'channelables.order:asc';
            }

            if ($channel->hide_title) {
                $config['hideTitle'] = true;
            }
            if ($channel->seo_title) {
                $config['seoTitle'] = $channel->seo_title;
            }
            if ($channel->seo_description) {
                $config['seoDescription'] = $channel->seo_description;
            }
            $channel->config = $config;
            $channel->save();
        }
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

    private function oldModelTypeToNew(string $old)
    {
        switch ($old) {
            case Track::class:
                return Track::MODEL_TYPE;
            case Album::class:
                return Album::MODEL_TYPE;
            case Artist::class:
                return Artist::MODEL_TYPE;
            case Channel::class:
                return Channel::MODEL_TYPE;
            case Genre::class:
                return Genre::MODEL_TYPE;
            case Playlist::class:
                return Playlist::MODEL_TYPE;
        }
    }
}
