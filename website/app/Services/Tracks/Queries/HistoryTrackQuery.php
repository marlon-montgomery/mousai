<?php

namespace App\Services\Tracks\Queries;

use App\Artist;
use App\Track;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryTrackQuery extends BaseTrackQuery
{
    const ORDER_COL = 'track_plays.created_at';

    public function get($userId)
    {
        return $this->baseQuery()
            // select latest row from track_plays when grouping by track_plays.track_id
            ->join(DB::raw('(select track_id, user_id, max(created_at) as created_at, id from track_plays group by track_id) as track_plays'), 'tracks.id', '=', 'track_plays.track_id')
            ->where('track_plays.user_id', $userId)
            ->select('tracks.*', 'track_plays.created_at as added_at');
    }
}
