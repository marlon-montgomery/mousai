<?php

namespace App\Services\Tracks\Queries;

use App\Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class LibraryTracksQuery extends BaseTrackQuery
{
    const ORDER_COL = 'added_at';

    public function get($userId)
    {
        return $this->baseQuery()
            ->join('likes', 'tracks.id', '=', 'likes.likeable_id')
            ->where('likes.user_id', $userId)
            ->where('likes.likeable_type', Track::class)
            ->select('tracks.*', 'likes.created_at as added_at');
    }
}