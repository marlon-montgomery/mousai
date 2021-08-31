<?php

namespace App\Http\Controllers\Artist;

use App\Artist;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class ArtistFollowersController extends BaseController
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

        $pagination = $artist->followers()
            ->withCount(['followers'])
            ->paginate(request('perPage') ?? 25);

        return $this->success(['pagination' => $pagination]);
    }
}
