<?php

namespace App\Http\Controllers\Artist;

use App\Artist;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class ArtistTracksController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(Artist $artist)
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [$artist, $userId]);

        $pagination = $artist->tracks()
            ->with('genres')
            ->withCount('plays')
            ->paginate($this->request->get('perPage') ?? 20);

        return $this->success(['pagination' => $pagination]);
    }
}
