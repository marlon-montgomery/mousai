<?php namespace App\Http\Controllers;

use App;
use App\Actions\Track\DeleteTracks;
use App\Artist;
use App\Http\Requests\ModifyArtists;
use App\Jobs\IncrementModelViews;
use App\Services\Albums\DeleteAlbums;
use App\Services\Artists\CrupdateArtist;
use App\Services\Artists\LoadArtist;
use App\Services\Artists\PaginateArtists;
use App\UserProfile;
use Common\Core\BaseController;
use Common\Files\Actions\Deletion\DeleteEntries;
use DB;
use Illuminate\Http\Request;

class ArtistController extends BaseController {

    /**
     * @var Request
     */
    private $request;

	public function __construct(Request $request)
	{
        $this->request = $request;
    }

	public function index()
	{
        $this->authorize('index', Artist::class);

        $pagination = app(PaginateArtists::class)->execute($this->request->all());

        $pagination->makeVisible(['updated_at', 'views', 'plays', 'verified']);

	    return $this->success(['pagination' => $pagination]);
	}

    public function show(Artist $artist)
    {
        $this->authorize('show', $artist);

        $response = app(LoadArtist::class)->execute($artist, $this->request->all(), $this->request->has('autoUpdate'));

        dispatch(new IncrementModelViews($artist->id, 'artist'));

        return $this->success($response);
    }

    public function store(ModifyArtists $validate)
    {
        $this->authorize('store', Artist::class);

        $artist = app(CrupdateArtist::class)->execute($this->request->all());

        return $this->success(['artist' => $artist]);
    }

	public function update(Artist $artist, ModifyArtists $validate)
	{
		$this->authorize('update', $artist);

        $artist = app(CrupdateArtist::class)->execute($this->request->all(), $artist);

        return $this->success(['artist' => $artist]);
	}

	public function destroy()
	{
        $artistIds = $this->request->get('ids');
		$this->authorize('destroy', [Artist::class, $artistIds]);

	    $this->validate($this->request, [
		    'ids'   => 'required|array',
		    'ids.*' => 'required|integer'
        ]);

        $artists = Artist::whereIn('id', $artistIds)->get();
        $imagePaths = $artists->pluck('image_small')
            ->concat($artists->pluck('image_large'))
            ->filter();
        app(DeleteEntries::class)->execute([
            'paths' => $imagePaths->toArray()
        ]);
        Artist::destroy($artists->pluck('id'));
        app(DeleteAlbums::class)->execute(
            DB::table('artist_album')->whereIn('artist_id', $artistIds)->where('primary', true)->pluck('album_id')
        );
        app(DeleteTracks::class)->execute(
            DB::table('artist_track')->whereIn('artist_id', $artistIds)->where('primary', true)->pluck('track_id')->toArray()
        );
        DB::table('user_artist')->whereIn('artist_id', $artistIds)->delete();
        DB::table('likes')->where('likeable_type', Artist::class)->whereIn('likeable_id', $artistIds)->delete();
        UserProfile::whereIn('artist_id', $artistIds)->delete();

		return $this->success();
	}
}
