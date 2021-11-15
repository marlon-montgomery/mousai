<?php

namespace App\Actions\Channel;

use App\Channel;
use App\Services\Providers\Lastfm\LastfmTopGenres;
use App\Services\Providers\Spotify\SpotifyNewAlbums;
use App\Services\Providers\Spotify\SpotifyPlaylist;
use App\Services\Providers\Spotify\SpotifyTopTracks;
use Arr;
use Cache;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UpdateChannelContent
{
    public function execute(Channel $channel)
    {
        $content = $this->getContent($channel);

        // bail if we could not fetch any content
        if ( ! $content || $content->isEmpty()) {
            return;
        }

        // detach all channel items from the channel
        DB::table('channelables')->where([
            'channel_id' => $channel->id,
        ])->delete();

        // group content by model type (track, album, playlist etc)
        // and attach each group via its own separate relation
        $groupedContent = $content->groupBy('model_type');
        $groupedContent->each(function(Collection $contentGroup, $modelType) use($channel) {
            $pivots = $contentGroup->mapWithKeys(function($item, $index) {
                return [$item['id'] => ['order' => $index]];
            });
            // track => tracks
            $relation = Str::plural($modelType);
            $channel->$relation()->syncWithoutDetaching($pivots->toArray());
        });

        Cache::forget("channels.$channel->id");
    }

    private function getContent(Channel $channel)
    {
        switch (Arr::get($channel->config, 'autoUpdateMethod')) {
            case 'spotifyTopTracks':
                return app(SpotifyTopTracks::class)->getContent();
            case 'spotifyNewAlbums':
                return app(SpotifyNewAlbums::class)->getContent();
            case 'lastfmTopGenres':
                return app(LastfmTopGenres::class)->getContent();
            case 'spotifyPlaylistTracks':
                return app(SpotifyPlaylist::class)->getContent($channel->config['autoUpdateValue'])['tracks'];
        }
    }
}
