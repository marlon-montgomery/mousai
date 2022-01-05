<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\Laravel\ServiceProvider;
use Common\Core\BaseController;
use File;
use Request;

class YoutubeLogController extends BaseController
{
    public function store()
    {
        $code = Request::get('code');
        $videoUrl = Request::get('videoUrl');
        $date = Carbon::now()->format('y:m:d h:i:s');
        File::append(storage_path('logs/youtube-client.log'), "[$date] Could not play '$videoUrl' because of '$code' error.\n");
        return $this->success();
    }
}
