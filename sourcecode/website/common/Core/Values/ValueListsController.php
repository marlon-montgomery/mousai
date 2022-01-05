<?php namespace Common\Core\Values;

use Common\Core\BaseController;
use Common\Localizations\Localization;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValueListsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(string $names)
    {
        $values = app(ValueLists::class)->get($names, $this->request->all());
        return $this->success($values);
    }
}
