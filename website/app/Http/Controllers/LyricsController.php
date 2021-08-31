<?php namespace App\Http\Controllers;

use App\Lyric;
use App\Services\Lyrics\AzLyricsProvider;
use App\Services\Lyrics\LyricsWikiaProvider;
use App\Services\Lyrics\OvhLyricsProvider;
use App\Track;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Common\Settings\Settings;
use Illuminate\Http\Request;

class LyricsController extends BaseController
{
    /**
     * @var Lyric
     */
    private $lyric;

    /**
     * @var Track
     */
    private $track;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Lyric $lyric, Track $track, Request $request)
    {
        $this->lyric = $lyric;
        $this->track = $track;
        $this->request = $request;
    }

    public function index()
    {
        $this->authorize('index', Lyric::class);

        $paginator = new MysqlDataSource($this->lyric, $this->request->all());
        return $this->success(['pagination' => $paginator->paginate()]);
    }

    public function show(int $trackId)
    {
        $this->authorize('show', Lyric::class);

        $lyric = $this->lyric->where('track_id', $trackId)->first();

        if (!$lyric) {
            $lyric = $this->fetchLyrics($trackId);
        }

        return $this->success(['lyric' => $lyric]);
    }

    public function store()
    {
        $this->authorize('store', Lyric::class);

        $this->validate($this->request, [
            'text' => 'required|string',
            'track_id' => 'required|integer|exists:tracks,id',
        ]);

        $lyric = $this->lyric->create([
            'track_id' => $this->request->get('track_id'),
            'text' => $this->request->get('text'),
        ]);

        return $this->success(['lyric' => $lyric]);
    }

    public function update(int $id)
    {
        $this->authorize('update', Lyric::class);

        $this->validate($this->request, [
            'text' => 'required|string',
            'track_id' => 'required|integer|exists:tracks,id',
        ]);

        $lyric = $this->lyric->findOrFail($id);

        $lyric->update([
            'track_id' => $this->request->get('track_id'),
            'text' => $this->request->get('text'),
        ]);

        return $this->success(['lyric' => $lyric]);
    }

    public function destroy()
    {
        $this->authorize('destroy', Lyric::class);

        $this->validate($this->request, [
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $this->lyric->destroy($this->request->get('ids'));

        return $this->success();
    }

    public function fetchLyrics(int $trackId)
    {
        $track = $this->track->with('album.artists')->findOrFail($trackId);

        $trackName = $track->name;
        $artistName = $track->artists->first()['name'];

        // Peace Sells - 2011 Remastered => Peace Sells
        $trackName = preg_replace('/ - [0-9]{4} Remastered/', '', $trackName);

        // Zero - From the Original Motion Picture "Ralph Breaks The Internet" => Zero
        $trackName = preg_replace(
            '/- From the Original Motion Picture.*?$/',
            '',
            $trackName,
        );

        // South of the Border (feat. Camila Cabello & Cardi B) => South of the Border
        $trackName = trim(explode('(feat.', $trackName)[0]);

        switch (app(Settings::class)->get('providers.lyrics')) {
            case 'ovh':
                $text = app(OvhLyricsProvider::class)->getLyrics(
                    $artistName,
                    $trackName,
                );
                break;
            case 'lyricswikia':
                $text = app(LyricsWikiaProvider::class)->getLyrics(
                    $artistName,
                    $trackName,
                );
                break;
            case 'azlyrics':
                $text = app(AzLyricsProvider::class)->getLyrics(
                    $artistName,
                    $trackName,
                );
                break;
            default:
                $text = null;
        }

        if (!$text) {
            abort(404);
        }

        return $this->lyric->create([
            'track_id' => $trackId,
            'text' => $text,
        ]);
    }
}
