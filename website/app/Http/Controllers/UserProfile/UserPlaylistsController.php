<?php namespace App\Http\Controllers\UserProfile;

use App;
use App\Playlist;
use App\User;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserPlaylistsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Playlist
     */
    private $playlist;

    public function __construct(Request $request, Playlist $playlist)
    {
        $this->request = $request;
        $this->playlist = $playlist;

        $this->middleware('auth', ['only' => ['follow', 'unfollow']]);
    }

    public function index(User $user): Response
    {
        $this->authorize('show', $user);

        $builder = $user
            ->playlists()
            ->with('editors')
            ->limit(20);

        $paginator = new MysqlDataSource($builder, $this->request->all());

        return $this->success(['pagination' => $paginator->paginate()]);
    }

    public function follow(int $id): Response
    {
        $playlist = $this->playlist->findOrFail($id);

        $this->authorize('show', $playlist);

        $this->request
            ->user()
            ->playlists()
            ->sync([$id], false);

        return $this->success();
    }

    public function unfollow(int $id): Response
    {
        $playlist = $this->request
            ->user()
            ->playlists()
            ->find($id);

        $this->authorize('show', $playlist);

        if ($playlist) {
            $this->request
                ->user()
                ->playlists()
                ->detach($id);
        }

        return $this->success();
    }
}
