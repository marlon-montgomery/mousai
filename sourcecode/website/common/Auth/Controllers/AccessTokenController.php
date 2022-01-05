<?php

namespace Common\Auth\Controllers;

use Auth;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class AccessTokenController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth']);
        $this->request = $request;
    }

    public function store()
    {
        $this->validate($this->request, [
            'tokenName' => 'required|string|min:3|max:100',
        ]);

        $token =  Auth::user()->createToken($this->request->get('tokenName'));

        return $this->success(['token' => $token->accessToken, 'plainTextToken' => $token->plainTextToken]);
    }

    public function destroy(string $tokenId)
    {
        Auth::user()->tokens()->where('id', $tokenId)->delete();

        return $this->success();
    }
}
