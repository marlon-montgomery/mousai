<?php namespace App\Services\Playlists;

use App\Services\Tracks\Queries\PlaylistTrackQuery;
use App\Track;
use Common\Database\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PlaylistTracksPaginator
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function paginate(int $playlistId): LengthAwarePaginator
    {
        $query = (new PlaylistTrackQuery([
            'orderBy' => $this->request->get('orderBy'),
            'orderDir' => $this->request->get('orderDir'),
        ]))->get($playlistId);

        $paginator = (new Paginator($query, $this->request->all()));
        $paginator->searchColumn = 'tracks.name';
        $paginator->defaultPerPage = 30;
        $paginator->dontSort = true;

        return $paginator->paginate();
    }
}
