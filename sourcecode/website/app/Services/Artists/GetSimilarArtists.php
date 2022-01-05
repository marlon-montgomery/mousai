<?php

namespace App\Services\Artists;

use App\Artist;
use Arr;
use DB;
use Illuminate\Support\Collection;

class GetSimilarArtists
{
    public function execute(Artist $artist, array $params = []): Collection
    {
        $genreIds = $artist->genres->pluck('id');

        if ($genreIds->isNotEmpty()) {
            return $this->getByGenres($genreIds, $artist->id, $params);
        }

        return collect();
    }

    private function getByGenres(Collection $genreIds, $artistId, $params): Collection
    {
        return Artist::select(DB::raw('artists.*, COUNT(*) AS tag_count'))
            ->join('genreables', 'genreable_id', '=', 'artists.id')
            ->whereIn('genreables.genre_id', $genreIds)
            ->where('genreables.genreable_type', Artist::class)
            ->where('artists.id', '!=', $artistId)
            ->groupBy('artists.id')
            ->orderBy('tag_count', 'desc')
            ->limit(Arr::get($params, 'limit', 10))
            ->get();
    }
}
