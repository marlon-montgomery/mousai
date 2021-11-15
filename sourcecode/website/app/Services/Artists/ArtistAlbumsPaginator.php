<?php namespace App\Services\Artists;

use App\Artist;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArtistAlbumsPaginator
{
    /**
     * Paginate all specified artist's albums.
     *
     * First order by number of tracks, so all albums
     * with less then 5 tracks (singles) are at
     * the bottom, then order by album release date.
     */
    public function paginate(Artist $artist, array $params): LengthAwarePaginator
    {
        $prefix = DB::getTablePrefix();

        return $artist
            ->albums()
            ->with('tracks.artists', 'artists')
            ->leftjoin('tracks', 'tracks.album_id', '=', 'albums.id')
            ->groupBy('albums.id')
            ->orderByRaw("COUNT({$prefix}tracks.id) > 5 desc, {$prefix}albums.release_date desc")
            ->paginate($params['albumsPerPage'] ?? 5);
    }
}
