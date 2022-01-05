<?php

namespace Common\Admin\Appearance\Themes;

use Common\Core\BaseController;
use Common\Database\Datasource\Datasource;
use Illuminate\Http\Request;

class CssThemeController extends BaseController
{
    /**
     * @var CssTheme
     */
    private $cssTheme;

    /**
     * @var Request
     */
    private $request;

    public function __construct(CssTheme $cssTheme, Request $request)
    {
        $this->cssTheme = $cssTheme;
        $this->request = $request;
    }

    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [CssTheme::class, $userId]);

        $builder = $this->cssTheme->newQuery();
        if ($userId) {
            $builder->where('user_id', $userId);
        }

        $dataSource = new Datasource($this->cssTheme, $this->request->all());
        $pagination = $dataSource->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    public function show(CssTheme $cssTheme)
    {
        $this->authorize('show', $cssTheme);

        return $this->success(['theme' => $cssTheme]);
    }

    public function store(CrupdateCssThemeRequest $request)
    {
        $this->authorize('store', CssTheme::class);

        $cssTheme = app(CrupdateCssTheme::class)->execute($request->all());

        return $this->success(['theme' => $cssTheme]);
    }

    public function update(CssTheme $cssTheme, CrupdateCssThemeRequest $request)
    {
        $this->authorize('store', $cssTheme);

        $cssTheme = app(CrupdateCssTheme::class)->execute($request->all(), $cssTheme);

        return $this->success(['theme' => $cssTheme]);
    }

    public function destroy(CssTheme $cssTheme)
    {
        $this->authorize('destroy', $cssTheme);

        $cssTheme->delete();

        return $this->success();
    }
}
