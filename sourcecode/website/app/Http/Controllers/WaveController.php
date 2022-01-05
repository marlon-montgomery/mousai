<?php

namespace App\Http\Controllers;

use App\Track;
use Common\Comments\Comment;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Storage;

class WaveController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Track
     */
    private $track;

    /**
     * @param Request $request
     * @param Track $track
     */
    public function __construct(Request $request, Track $track)
    {
        $this->request = $request;
        $this->track = $track;
    }

    public function show($trackId)
    {
        try {
            $waveData = json_decode($this->track->getWaveStorageDisk()->get("waves/$trackId.json"), true);
        } catch (FileNotFoundException $e) {
            $waveData = [];
        }

        $comments = app(Comment::class)
            ->where('commentable_id', $trackId)
            ->where('commentable_type', Track::class)
            ->rootOnly()
            ->with('user')
            ->limit(50)
            ->groupBy('position')
            ->orderBy('position', 'asc')
            ->get()
            ->map(function(Comment $comment) {
                $comment->relative_created_at = $comment->created_at->diffForHumans();
                return $comment;
            });

        return $this->success([
            'waveData' => $waveData,
            'comments' => $comments
        ]);
    }
}
