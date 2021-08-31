<?php namespace Common\Pages;

use Auth;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Str;

class CustomPageController extends BaseController
{
    /**
     * @var CustomPage
     */
    private $page;

    /**
     * @var Request
     */
    private $request;

    /**
     * CustomPage model might get overwritten with
     * parent page model, for example, LinkPage
     */
    public function __construct(CustomPage $page, Request $request)
    {
        $this->page = $page;
        $this->request = $request;
    }

    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [get_class($this->page), $userId]);

        $builder = $this->page->newQuery();

        // make sure we filter by page type on full text engines for
        // example, only search "link page" on "meilisearch" on "belink"
        $modelType = $this->page::PAGE_TYPE;
        $pageType = $this->request->get(
            'type',
            $modelType !== 'default' ? $modelType : null,
        );
        if ($pageType) {
            $builder->where('type', '=', $pageType);
        }

        if ($userId) {
            $builder->where('user_id', '=', $userId);
        }

        $pagination = (new MysqlDataSource($builder, $this->request->all()))
            ->paginate()
            ->toArray();

        $pagination['data'] = array_map(function ($page) {
            $page['body'] = Str::limit(strip_tags($page['body']), 50);
            return $page;
        }, $pagination['data']);

        return $this->success(['pagination' => $pagination]);
    }

    public function show(int $id)
    {
        $page = $this->page->findOrFail($id);
        $this->authorize('show', $page);

        return $this->success(['page' => $page]);
    }

    public function store()
    {
        $this->authorize('store', get_class($this->page));

        $validatedData = $this->validate($this->request, [
            'title' => [
                'string',
                'min:3',
                'max:250',
                Rule::unique('custom_pages')->where('user_id', Auth::id()),
            ],
            'slug' => [
                'nullable',
                'string',
                'min:3',
                'max:250',
                Rule::unique('custom_pages'),
            ],
            'body' => 'required|string|min:1',
            'hide_nav' => 'boolean',
        ]);

        $page = app(CrupdatePage::class)->execute(
            $this->page->newInstance(),
            $validatedData,
        );

        return $this->success(['page' => $page]);
    }

    public function update(int $id)
    {
        $page = $this->page->findOrFail($id);
        $this->authorize('update', $page);

        $validatedData = $this->validate($this->request, [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:250',
                Rule::unique('custom_pages')
                    ->where('user_id', $page->user_id)
                    ->ignore($page->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'min:3',
                'max:250',
                Rule::unique('custom_pages')->ignore($page->id),
            ],
            'body' => 'required|string|min:1',
            'hide_nav' => 'boolean',
        ]);

        $page = app(CrupdatePage::class)->execute($page, $validatedData);

        return $this->success(['page' => $page]);
    }

    public function destroy(string $ids)
    {
        $pageIds = explode(',', $ids);
        $this->authorize('destroy', [get_class($this->page), $pageIds]);

        $this->page->whereIn('id', $pageIds)->delete();

        return $this->success();
    }
}
