<?php

namespace Common\Admin;

use Auth;
use Common\Core\BaseController;

class ImpersonateUserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('isAdmin');
    }

    public function impersonate(int $userId)
    {
        Auth::loginUsingId($userId);

        return $this->success();
    }

    public function stopImpersonating()
    {
        Auth::logout();

        return $this->success();
    }
}
