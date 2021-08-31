<?php

namespace App\Http\Controllers\Search;

use App\Album;
use App\Track;
use Auth;
use Common\Core\BaseController;
use Symfony\Component\HttpFoundation\Response;

class AlbumSearchSuggestionsController extends BaseController
{
    public function index(): Response
    {
        $this->authorize('index', Album::class);

        $limit = request('limit', 10);
        $query = request('query');
        $user = Auth::user();

        $builder = $user->hasPermission('music.update') || $user->getRestrictionValue('music.create', 'artist_selection') ?
            Album::query() :
            $user->primaryArtist()->albums();

        $albums = $builder->where('name', 'like', $query.'%')
            ->limit($limit)
            ->with('artists')
            ->select(['albums.id', 'name', 'image'])
            ->get();

        return $this->success(['albums' => $albums]);
    }
}
