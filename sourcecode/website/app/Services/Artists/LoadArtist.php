<?php

namespace App\Services\Artists;

use App\Artist;
use App\Services\Providers\ProviderResolver;
use App\Services\Providers\Spotify\SpotifyArtist;
use Arr;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LoadArtist
{
    public function execute(Artist $artist, array $params = [], $autoUpdate = false): array
    {
        if ($autoUpdate && $artist->needsUpdating()) {
            $newArtist = $this->updateArtistFromExternal($artist);
            $artist = $newArtist ?? $artist;
        }

        if (Arr::get($params, 'defaultRelations') || defined('SHOULD_PRERENDER')) {
            $load = ['similar', 'genres', 'fullAlbums', 'profile', 'topTracks'];
            $loadCount = [];
            if (app(Settings::class)->get('artistPage.showFollowers')) {
                $loadCount[] = 'followers';
            }
        } else {
            $load = array_filter(explode(',', Arr::get($params, 'with', '')));
            $loadCount = array_filter(explode(',', Arr::get($params, 'withCount', '')));
        }

        if (Arr::get($params, 'forEditing')) {
            $artist->makeVisible(['spotify_id']);
        }

        $artist->loadCount($loadCount);

        $response = ['artist' => $artist];

        foreach ($load as $relation) {
            if ($relation === 'similar') {
                if (app(Settings::class)->get('artist_provider', 'local') === 'local') {
                    $similar = app(GetSimilarArtists::class)->execute($artist);
                    $artist->setRelation('similar', $similar);
                } else {
                    $artist->load('similar');
                }
            } else if ($relation === 'simplifiedAlbums') {
                $artist->load(['albums' => function(BelongsToMany $builder) {
                    $builder->orderBy('created_at', 'desc')->withCount('tracks');
                }]);
                $response['albums'] = $artist->albums->makeVisible(['views']);
            } else if ($relation === 'fullAlbums') {
                $response['albums'] = app(ArtistAlbumsPaginator::class)->paginate($artist, $params);
            } else if ($relation === 'profile') {
                $artist->load(['profile', 'profileImages', 'links']);
            } else {
                try {
                    $artist->load($relation);
                } catch (RelationNotFoundException $e) {
                    //
                }
            }
        }

        return $response;
    }

    public function updateArtistFromExternal(Artist $artist, ?array $options = []): Artist
    {
        $spotifyArtist = app(SpotifyArtist::class)->getContent($artist, $options);
        if ($spotifyArtist) {
            $artist = app(ArtistSaver::class)->save($spotifyArtist);
            $artist = app(ArtistBio::class)->get($artist);
            unset($artist['albums']);
        }
        return $artist;
    }
}
