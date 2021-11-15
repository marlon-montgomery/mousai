<?php namespace App\Http\Controllers;

use App;
use App\Http\Requests\ModifyPlaylist;
use App\Jobs\IncrementModelViews;
use App\Playlist;
use App\Services\Playlists\DeletePlaylists;
use App\Services\Playlists\PaginatePlaylists;
use App\Services\Playlists\PlaylistTracksPaginator;
use Auth;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlaylistController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Playlist
     */
    private $playlist;

    /**
     * @var PlaylistTracksPaginator
     */
    private $tracksPaginator;

    public function __construct(
        Request $request,
        Playlist $playlist,
        PlaylistTracksPaginator $tracksPaginator
    )
    {
        $this->request = $request;
        $this->playlist = $playlist;
        $this->tracksPaginator = $tracksPaginator;
    }

    public function index()
    {
        $this->authorize('index', Playlist::class);

        $pagination = app(PaginatePlaylists::class)->execute($this->request->all());

        $pagination->makeVisible(['updated_at', 'views', 'description']);

        return $this->success(['pagination' => $pagination]);
    }

    public function show(int $id)
    {
        $playlist = $this->playlist
            ->with('editors')
            ->withCount('tracks')
            ->findOrFail($id);

        $playlist->makeVisible(['description']);

        $this->authorize('show', $playlist);

        $totalDuration = $playlist->tracks()->sum('tracks.duration');

        dispatch(new IncrementModelViews($playlist->id, 'playlist'));

        return $this->success([
            'playlist' => $playlist->toArray(),
            'tracks' => $this->tracksPaginator->paginate($playlist->id),
            'totalDuration' => (int) $totalDuration
        ]);
    }

    public function store(ModifyPlaylist $validate): Response
    {
        $this->authorize('store', Playlist::class);

        $params = $this->request->all();
        $params['owner_id'] = Auth::id();
        Playlist::unguard();
        $newPlaylist = $this->request->user()->playlists()->create($params, ['editor' => true]);

        $newPlaylist->load('editors');

        return $this->success(['playlist' => $newPlaylist]);
    }

    public function update(Playlist $playlist, ModifyPlaylist $validate): Response
    {
        $this->authorize('update', $playlist);

        $playlist->fill($this->request->all())->save();
        $playlist->load('editors');
        $playlist->loadCount('tracks');
        $playlist->makeVisible(['description']);

        return $this->success(['playlist' => $playlist]);
    }

    public function destroy(): Response
    {
        $ids = $this->request->get('ids');
        $playlists = $this->playlist->with('editors')->whereIn('id', $ids)->get();

        $this->authorize('destroy', [Playlist::class, $playlists]);

        app(DeletePlaylists::class)->execute($playlists);

        return $this->success();
    }

    /**
     * Follow playlist with currently logged in user.
     *
     * @param int $id
     * @return integer
     */
    public function follow($id)
    {
        $playlist = $this->playlist->findOrFail($id);

        $this->authorize('show', $playlist);

        return $this->request->user()->playlists()->sync([$id], false);
    }

    /**
     * Un-Follow playlist with currently logged in user.
     *
     * @param integer $id
     * @return JsonResponse
     */
    public function unfollow($id)
    {
        $playlist = $this->request->user()->playlists()->find($id);

        $this->authorize('show', $playlist);

        if ($playlist) {
            $this->request->user()->playlists()->detach($id);
        }

        return $this->success();
    }
}
