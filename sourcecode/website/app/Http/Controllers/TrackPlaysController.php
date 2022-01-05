<?php namespace App\Http\Controllers;

use App;
use App\Jobs\LogTrackPlay;
use App\Services\Tracks\Queries\HistoryTrackQuery;
use App\Track;
use Carbon\Carbon;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TrackPlaysController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
	{
        $this->request = $request;
    }

    public function index($userId)
    {
        $orderBy = $this->request->get('orderBy');
        $orderDir = $this->request->get('orderDir');
        // prevent ambiguous column db error
        if ($orderBy === 'created_at') {
            $orderBy = 'track_plays.created_at';
        }

        $query = (new HistoryTrackQuery([
            'orderBy' => $orderBy,
            'orderDir' => $orderDir,
        ]))->get($userId);
        $paginator = (new Paginator($query, $this->request->all()));
        $paginator->dontSort = true;
        $paginator->defaultPerPage = 30;

        $paginator->searchCallback = function(Builder $builder, $query) {
            $builder->where('tracks.name', 'LIKE', $query.'%');
        };

        $pagination = $paginator->paginate();

        $pagination->transform(function(Track $track) {
            $track->added_at = $track->added_at ? (new Carbon($track->added_at))->diffForHumans() : null;
            return $track;
        });

        return $this->success(['pagination' => $pagination]);

    }

    public function create(Track $track)
    {
        $this->authorize('show', $track);

        LogTrackPlay::dispatch($track, $this->request->get('queueId'));

        return $this->success();
    }
}
