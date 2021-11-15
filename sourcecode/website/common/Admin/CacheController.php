<?php

namespace Common\Admin;

use Artisan;
use Cache;
use Common\Core\BaseController;
use Common\Settings\Setting;
use Illuminate\Http\Request;

class CacheController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->middleware('isAdmin');
    }

    public function flush()
    {
        Cache::flush();

        return $this->success();
    }
}
