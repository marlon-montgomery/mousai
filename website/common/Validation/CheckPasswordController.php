<?php namespace Common\Validation;

use Common\Core\BaseController;
use DB;
use Hash;
use Illuminate\Http\Request;

class CheckPasswordController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function check()
    {
        $this->validate($this->request, [
            'table' => 'required|string',
            'id' => 'required|integer',
            'password' => 'required|string',
        ]);

        $record = DB::table($this->request->get('table'))
            ->find($this->request->get('id'));
        $matches = Hash::check($this->request->get('password'), $record->password);

        return $this->success(['matches' => $matches]);
    }
}
