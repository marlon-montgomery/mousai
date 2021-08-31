<?php

namespace App\Http\Controllers;

use App\Tag;
use App\Track;
use Common\Core\BaseController;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagMediaController extends BaseController
{
    /**
     * @var Tag
     */
    private $tag;

    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }

    public function index($tagName, $mediaType = 'tracks')
    {
        $tag = $this->tag->where('name', $tagName)->firstOrFail();

        $this->authorize('show', $tag);

        $response = [
            'tag' => $tag,
        ];

        if ($mediaType === 'tracks') {
            $response['tracks'] = $tag->tracks()->with('artists')->paginate(15);
        } else if ($mediaType === 'albums') {
            $response['albums'] = $tag
                ->albums()
                ->withCount('plays')
                ->with(['artists', 'tracks' => function(HasMany $query) {
                    $query->orderBy('number', 'desc')
                        ->select('tracks.id', 'album_id', 'name', 'plays', 'image', 'url', 'duration');
                }])
                ->paginate(15);
        }

        return $this->success($response);
    }
}
