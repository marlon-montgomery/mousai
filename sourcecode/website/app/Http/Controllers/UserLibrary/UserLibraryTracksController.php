<?php namespace App\Http\Controllers\UserLibrary;

use App\Services\Tracks\Queries\LibraryTracksQuery;
use App\Track;
use App\User;
use Auth;
use Carbon\Carbon;
use Common\Core\BaseController;
use Common\Database\Paginator;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserLibraryTracksController extends BaseController {

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->middleware('auth');

        $this->request = $request;
    }

    public function addToLibrary()
    {
        $likeables = collect($this->request->get('likeables'))
            ->map(function($likeable) {
                $likeable['user_id'] = Auth::user()->id;
                $likeable['created_at'] = Carbon::now();
                // track => App\Track
                $likeable['likeable_type'] = modelTypeToNamespace($likeable['likeable_type']);
                return $likeable;
            });
        DB::table('likes')->insert($likeables->toArray());
        return $this->success();
    }

    public function removeFromLibrary()
    {
        $this->validate($this->request, [
            'likeables.*.likeable_id' => 'required|int',
            'likeables.*.likeable_type' => 'required|in:track,album,artist',
        ]);

        $userId = Auth::id();
        $values = collect($this->request->get('likeables'))->map(function($likeable) use($userId) {
            // track => App\Track
            $likeableType = 'App\\\\' . ucfirst($likeable['likeable_type']);
            return "('$userId', '{$likeable['likeable_id']}', '{$likeableType}')";
        })->implode(', ');
        DB::table('likes')->whereRaw("(user_id, likeable_id, likeable_type) in ($values)")->delete();
        return $this->success();
    }


    public function index(User $user = null)
    {
        $user = $user ?? Auth::user();
        $this->authorize('show', $user);

        $query = (new LibraryTracksQuery([
            'orderBy' => $this->request->get('orderBy'),
            'orderDir' => $this->request->get('orderDir'),
        ]))->get($user->id);
        $paginator = (new Paginator($query, $this->request->all()));
        $paginator->dontSort = true;
        $paginator->defaultPerPage = 30;

        $paginator->searchCallback = function(Builder $builder, $query) {
            $builder->where(function($builder) use($query) {
                $builder->where('name', 'LIKE', $query.'%');
                $builder->orWhereHas('album', function(Builder $q) use($query) {
                    return $q->where('name', 'LIKE', $query.'%')
                        ->orWhereHas('artists', function(Builder $q) use($query) {
                            return $q->where('name', 'LIKE', $query.'%');
                        });
                });
            });
        };

        $pagination = $paginator->paginate();

        $pagination->transform(function(Track $track) {
            $track->added_at = $track->added_at ? (new Carbon($track->added_at))->diffForHumans() : null;
            return $track;
        });

        return $this->success(['pagination' => $pagination]);
    }
}
