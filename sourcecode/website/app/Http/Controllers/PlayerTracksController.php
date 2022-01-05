<?php

namespace App\Http\Controllers;

use App\Album;
use App\Artist;
use App\Playlist;
use App\Services\Tracks\Queries\AlbumTrackQuery;
use App\Services\Tracks\Queries\ArtistTrackQuery;
use App\Services\Tracks\Queries\BaseTrackQuery;
use App\Services\Tracks\Queries\HistoryTrackQuery;
use App\Services\Tracks\Queries\LibraryTracksQuery;
use App\Services\Tracks\Queries\PlaylistTrackQuery;
use App\User;
use Common\Core\BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PlayerTracksController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    private $queryMap = [
        Playlist::MODEL_TYPE => PlaylistTrackQuery::class,
        Artist::MODEL_TYPE => ArtistTrackQuery::class,
        User::MODEL_TYPE => LibraryTracksQuery::class,
        Album::MODEL_TYPE => AlbumTrackQuery::class,
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function index()
    {
        $queueId = $this->request->get('queueId');
        $perPage = (int) $this->request->get('perPage', 25);
        list($modelType, $modelId, $queueType, $queueOrder) = array_pad(explode('.', $queueId), 4, null);

        $trackQuery = $this->getTrackQuery($modelType, $queueOrder, $queueType);

        if ( ! $trackQuery) {
            return $this->success(['tracks' => []]);
        }

        $dbQuery = $trackQuery->get($modelId);
        $order = $trackQuery->getOrder();

        if ($lastTrack = $this->request->get('lastTrack')) {
            $whereCol = $order['col'] === 'added_at' ? 'likes.created_at' : $order['col'];
            $this->applyCursor($dbQuery, [$whereCol => $order['dir'], 'tracks.id' => 'desc'], [$lastTrack[$order['col']], $lastTrack['id']]);
            // TODO: check if playlist position should be asc or desc
        }

        return $this->success(['tracks' => $dbQuery->limit($perPage)->get()]);
    }

    /**
     * @return BaseTrackQuery|void
     */
    private function getTrackQuery(string $modelType, ?string $order, string $queueType)
    {
        $params = [];
        if ($order) {
            $parts = explode('|', $order);
            $params['orderBy'] = $parts[0];
            $params['orderDir'] = $parts[1];
        }

        if ($modelType === User::MODEL_TYPE) {
            return $queueType === 'playHistory' ?
                new HistoryTrackQuery($params) :
                new LibraryTracksQuery($params);
        }

        if (isset($this->queryMap[$modelType])) {
            return new $this->queryMap[$modelType]($params);
        }
    }

    private function applyCursor(Builder $query, $columns, $cursor)
    {
        $query->where(function (Builder $query) use ($columns, $cursor) {
            $column = key($columns);
            $direction = array_shift($columns);
            $value = array_shift($cursor);

            $query->where($column, $direction === 'asc' ? '>' : '<', (is_null($value) ? 0 : $value));

            if ( ! empty($columns)) {
                $query->orWhere($column, (is_null($value) ? 0 : $value));
                $this->applyCursor($query, $columns, $cursor);
            }
        });
    }
}
