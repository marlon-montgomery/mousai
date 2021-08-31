<?php namespace App\Http\Controllers\UserLibrary;

use App\User;
use Auth;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Http\Request;

class UserLibraryAlbumsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->middleware('auth');

        $this->request = $request;
    }

    public function index(User $user = null)
    {
        $user = $user ?? Auth::user();

        $this->authorize('show', $user);

        $builder = $user
            ->likedAlbums()
            ->with('artists')
            ->limit(25);

        $paginator = new MysqlDataSource($builder, $this->request->all());
        $paginator->order = [
            'col' => 'likes.created_at',
            'dir' => 'desc',
        ];

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }
}
