<?php namespace App\Http\Controllers;

use App;
use App\Artist;
use App\Services\Artists\ArtistAlbumsPaginator;
use Common\Core\BaseController;

class ArtistAlbumsController extends BaseController {

    /**
     * @var ArtistAlbumsPaginator
     */
    private $paginator;

	public function __construct(ArtistAlbumsPaginator $paginator)
	{
        $this->paginator = $paginator;
    }

	public function index(Artist $artist)
	{
		$this->authorize('show', $artist);

	    return $this->success(['pagination' => $this->paginator->paginate($artist, request()->all())]);
	}
}
