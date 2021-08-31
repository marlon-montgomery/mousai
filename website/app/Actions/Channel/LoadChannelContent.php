<?php

namespace App\Actions\Channel;

use App\Album;
use App\Artist;
use App\Channel;
use App\Genre;
use App\Playlist;
use App\Services\Albums\PaginateAlbums;
use App\Services\Artists\PaginateArtists;
use App\Services\Genres\PaginateGenres;
use App\Services\Playlists\PaginatePlaylists;
use App\Services\Tracks\PaginateTracks;
use App\Track;
use App\User;
use Arr;
use Cache;
use Common\Core\Prerender\Actions\ReplacePlaceholders;
use Common\Database\Paginator;
use Common\Settings\Settings;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\Paginator as SimplePaginator;
use Illuminate\Support\Collection;

class LoadChannelContent
{
    public function execute(
        Channel $channel,
        array $params = [],
        Channel $parent = null
    ): ?AbstractPaginator {
        $params['perPage'] = $params['perPage'] ?? 50;
        $params['order'] =
            $params['order'] ?? Arr::get($channel->config, 'contentOrder');

        $this->maybeConnectToGenre($channel, $params, $parent);

        if ($paginator = $this->maybeLoadViaQuery($channel, $params)) {
            return $paginator;
        } else {
            // TODO: remove null filters, "forAdmin", other irrelevant params
            $paramsKey = json_encode($params);
            return Cache::remember(
                // use "updated at" so channel changes from admin area will automatically
                // cause new cache item, without having to clear cache manually
                "channels.$channel->id.$channel->updated_at.$paramsKey",
                1440,
                function () use ($channel, $params, $parent) {
                    return $this->load($channel, $params);
                },
            );
        }
    }

    private function maybeLoadViaQuery(
        Channel $channel,
        array $params
    ): ?AbstractPaginator {
        if (Arr::get($channel->config, 'contentType') === 'listAll') {
            try {
                switch (Arr::get($channel->config, 'contentModel')) {
                    case Artist::MODEL_TYPE:
                        return app(PaginateArtists::class)->execute(
                            $params,
                            $channel->genre,
                        );
                    case Album::MODEL_TYPE:
                        return app(PaginateAlbums::class)->execute(
                            $params,
                            $channel->genre,
                        );
                    case Track::MODEL_TYPE:
                        return app(PaginateTracks::class)->execute(
                            $params,
                            $channel->genre,
                        );
                    case Genre::MODEL_TYPE:
                        return app(PaginateGenres::class)->execute($params);
                    case Playlist::MODEL_TYPE:
                        $builder = Playlist::where('public', true)->has(
                            'tracks',
                        );
                        return app(PaginatePlaylists::class)->execute(
                            $params,
                            $builder,
                        );
                }
            } catch (Exception $e) {
                //
            }
        }
        return null;
    }

    private function load(Channel $channel, array $params): ?AbstractPaginator
    {
        // channel consists of single model type only, can use laravel relation to load records
        if ($modelType = Arr::get($channel, 'config.contentModel')) {
            $builder = $this->getQueryBuilderFor($modelType, $channel);
            if (!$builder) {
                return null;
            }

            if (Arr::get($params, 'simplePagination')) {
                $items = $builder
                    ->limit($params['perPage'])
                    ->orderBy('channelables.order')
                    ->get();
                $pagination = new SimplePaginator(
                    $items,
                    $params['perPage'],
                    1,
                );
            } else {
                $paginator = new Paginator($builder, $params);
                $order = $paginator->getOrder();
                if ($order['col'] === 'popularity') {
                    $paginator->dontSort = true;
                    $paginator->query()->orderByPopularity($order['dir']);
                }

                $pagination = $items = $paginator->paginate();
            }

            $items->transform(function (Model $model) use ($channel, $params) {
                $model['channelable_id'] = $model->pivot->id;
                $model['channelable_order'] = $model->pivot->order;
                if ($model instanceof Channel) {
                    // only load 10 items per nested channel
                    $model->loadContent(
                        array_merge($params, [
                            'perPage' => 10,
                            'simplePagination' => true,
                            'order' => null,
                        ]),
                        $channel,
                    );
                }
                return $model;
            });

            return $pagination;

            // otherwise will need to load pivot table first and then one query per model type
        } else {
            $pagination = DB::table('channelables')
                ->where('channel_id', $channel->id)
                ->orderBy('order')
                ->paginate($params['perPage']);
            $newCollection = $pagination
                ->getCollection()
                ->groupBy('channelable_type')
                ->map(function (
                    Collection $channelableGroup,
                    string $channelableModel
                ) {
                    return $this->getQueryBuilderFor(
                        app($channelableModel)::MODEL_TYPE,
                    )
                        ->whereIn(
                            'id',
                            $channelableGroup->pluck('channelable_id'),
                        )
                        ->get()
                        ->map(function (Model $model) use ($channelableGroup) {
                            $channelable = $channelableGroup->first(function (
                                $channelable
                            ) use ($model) {
                                return (int) $channelable->channelable_id ===
                                    $model['id'] &&
                                    $channelable->channelable_type ===
                                        get_class($model);
                            });
                            $model['channelable_id'] = $channelable->id;
                            $model['channelable_order'] = $channelable->order;
                            return $model;
                        });
                })
                ->flatten(1)
                ->sortBy('channelable_order')
                ->values();
            $pagination->setCollection($newCollection);
            return $pagination;
        }
    }

    /**
     * @return Builder
     */
    private function getQueryBuilderFor(
        string $modelType,
        Channel $channel = null
    ) {
        switch ($modelType) {
            case Track::MODEL_TYPE:
                return ($channel ? $channel->tracks() : app(Track::class))
                    ->with('album.artists', 'artists', 'genres')
                    ->withCount('plays');
            case Album::MODEL_TYPE:
                return ($channel
                    ? $channel->albums()
                    : app(Album::class)
                )->with('artists');
            case Artist::MODEL_TYPE:
                return ($channel
                    ? $channel->artists()
                    : app(Artist::class)
                )->select(['name', 'artists.id', 'image_small']);
            case User::MODEL_TYPE:
                return ($channel
                    ? $channel->users()
                    : app(User::class)
                )->select([
                    'users.id',
                    'email',
                    'first_name',
                    'last_name',
                    'username',
                    'avatar',
                ]);
            case Genre::MODEL_TYPE:
                return $channel ? $channel->genres() : app(Genre::class);
            case Playlist::MODEL_TYPE:
                return ($channel
                    ? $channel->playlists()
                    : app(Playlist::class)
                )->with('editors');
            case Channel::MODEL_TYPE:
                return $channel ? $channel->channels() : app(Channel::class);
        }
    }

    private function maybeConnectToGenre(
        Channel $channel,
        array $params = [],
        Channel $parent = null
    ) {
        $channelToCheck = $parent ?? $channel;
        if (
            !Arr::get($params, 'forAdmin') &&
            Arr::get($channelToCheck->config, 'connectToGenreViaUrl')
        ) {
            $filter = Arr::get($params, 'filter');
            if (!$filter) {
                abort(404);
            }
            $genre =
                $parent->genre ??
                Genre::whereName($filter)
                    ->select(['id', 'name', 'display_name'])
                    ->firstOrFail();
            $channel->setAttribute('genre', $genre);

            $channel->name =
                app(ReplacePlaceholders::class)->execute($channel->name, [
                    'channel' => $channel,
                ]) ?:
                $channel->name;

            if (
                !$parent &&
                app(Settings::class)->get('artist_provider') !== 'local'
            ) {
                $channel->config = array_merge($channel->config, [
                    'actions' => [
                        [
                            'icon' => 'antenna',
                            'tooltip' => 'Genre radio',
                            'route' => "/radio/genre/{$channel->genre->id}/{$channel->genre->name}",
                        ],
                    ],
                ]);
            }
        }
    }
}
