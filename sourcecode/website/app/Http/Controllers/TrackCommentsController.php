<?php

namespace App\Http\Controllers;

use App\Services\Tracks\PaginateModelComments;
use App\Track;
use Common\Core\BaseController;

class TrackCommentsController extends BaseController
{
    public function index(Track $track)
    {
        $this->authorize('show', $track);

        $pagination = app(PaginateModelComments::class)->execute($track);

        return $this->success(['pagination' => $pagination]);
    }
}
