<?php namespace App\Http\Controllers\UserLibrary;

use App\User;
use Auth;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Http\Request;

class UserLibraryArtistsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(User $user = null)
    {
        $user = $user ?? Auth::user();
        $this->authorize('show', $user);

        $builder = $user->likedArtists()->limit(25);

        $paginator = new MysqlDataSource($builder, $this->request->all());
        $paginator->order = [
            'col' => 'likes.created_at',
            'dir' => 'desc',
        ];

        // TODO: if order col created_at order by likes.created_at

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }
}
