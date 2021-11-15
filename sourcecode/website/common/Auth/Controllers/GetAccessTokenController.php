<?php

namespace Common\Auth\Controllers;

use Common\Core\BaseController;
use Common\Core\Bootstrap\MobileBootstrapData;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class GetAccessTokenController extends BaseController
{
    use AuthenticatesUsers;

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string|email_verified',
            'password' => 'required|string',
            'token_name' => 'required|string|min:3|max:100',
        ]);
    }

    protected function sendLoginResponse(Request $request)
    {
        $bootstrapData = app(MobileBootstrapData::class)
            ->init()
            ->refreshToken($request->get('token_name'))
            ->get();
        return $this->success($bootstrapData);
    }
}
