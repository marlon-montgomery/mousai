<?php namespace App\Http\Controllers;

use DB;
use App\Playlist;
use Illuminate\Http\Request;
use Common\Core\BaseController;
use Symfony\Component\HttpFoundation\Response;

class PlaylistTracksOrderController extends BaseController {

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function change(Playlist $playlist): Response {

        $this->authorize('update', $playlist);

        $this->validate($this->request, [
            'ids'   => 'array|min:1',
            'ids.*' => 'integer'
        ]);

        $queryPart = '';
        foreach($this->request->get('ids') as $position => $id) {
            $position++;
            $queryPart .= " when track_id=$id then $position";
        }

        DB::table('playlist_track')
            ->whereIn('track_id', $this->request->get('ids'))
            ->update(['position' => DB::raw("(case $queryPart end)")]);

        return $this->success();
    }
}
