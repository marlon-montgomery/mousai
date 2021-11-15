<?php namespace App\Http\Controllers;

use App;
use App\Actions\Track\DeleteTracks;
use App\Http\Requests\ModifyTracks;
use App\Services\Tracks\CrupdateTrack;
use App\Services\Tracks\PaginateModelComments;
use App\Services\Tracks\PaginateTracks;
use App\Track;
use Arr;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class TrackController extends BaseController {

	/**
	 * @var Track
	 */
	private $track;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Track $track, Request $request)
	{
		$this->track = $track;
        $this->request = $request;
    }

	public function index()
	{
        $this->authorize('index', Track::class);

	    $pagination = App(PaginateTracks::class)->execute($this->request->all());

        $pagination->makeVisible(['views', 'updated_at', 'plays']);

	    return $this->success(['pagination' => $pagination]);
	}

	public function show(Track $track)
	{
        $this->authorize('show', $track);

        $params = $this->request->all();
        if ($this->request->get('defaultRelations') || defined('SHOULD_PRERENDER')) {
            $load = ['tags', 'genres', 'artists', 'fullAlbum', 'comments'];
            $loadCount = ['reposts', 'likes'];
        } else {
            $load = array_filter(explode(',', Arr::get($params, 'with', '')));
            $loadCount = array_filter(explode(',', Arr::get($params, 'withCount', '')));
        }

        if (Arr::get($params, 'forEditing')) {
            $track->makeVisible(['spotify_id']);
        }

        $response = ['track' => $track];
        foreach ($load as $relation) {
            if ($relation === 'fullAlbum') {
                $track->load(['album' => function(BelongsTo $builder) {
                    return $builder->with(['artists', 'tracks.artists']);
                }]);
            } else if ($relation === 'comments') {
                if (app(Settings::class)->get('player.track_comments')) {
                    $track->loadCount('comments');
                    $response['comments'] = app(PaginateModelComments::class)->execute($track);
                }
            } else {
                $track->load($relation);
            }
        }

        $track->loadCount($loadCount);

        if ($track->relationLoaded('album') && $track->album) {
            $track->album->addPopularityToTracks();
        }

        $track->makeVisible('description');

	    return $this->success($response);
	}

    public function store(ModifyTracks $validate)
    {
        $this->authorize('store', Track::class);

        $track = app(CrupdateTrack::class)
            ->execute($this->request->all(), null, $this->request->get('album'));

        return $this->success(['track' => $track]);
    }

    public function update(int $id, ModifyTracks $validate)
    {
        $track = $this->track->findOrFail($id);

        $this->authorize('update', $track);

        $track = app(CrupdateTrack::class)
            ->execute($this->request->all(), $track, $this->request->get('album'));

        return $this->success(['track' => $track]);
    }

	public function destroy()
	{
		$trackIds = $this->request->get('ids');
	    $this->authorize('destroy', [Track::class, $trackIds]);

        $this->validate($this->request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|integer'
        ]);

        app(DeleteTracks::class)->execute($trackIds);

	    return $this->success();
	}
}
