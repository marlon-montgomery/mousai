<?php

namespace Common\Tags;

use App\Tag as AppTag;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use DB;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index()
    {
        $this->authorize('index', Tag::class);

        $builder = $this->getModel()->newQuery();
        if ($type = request('type')) {
            $builder->where('type', '=', $type);
        }

        if ($notType = request('notType')) {
            $builder->where('type', '!=', $notType);
        }

        $dataSource = (new MysqlDataSource($this->getModel(), $this->request->all()));

        $pagination = $dataSource->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    public function store()
    {
        $this->authorize('store', Tag::class);

        $this->validate($this->request, [
            'name' => 'required|string|min:2|unique:tags',
            'display_name' => 'string|min:2',
            'type' => 'required|string|min:2',
        ]);

        $tag = $this->getModel()->create([
            'name' => $this->request->get('name'),
            'display_name' => $this->request->get('display_name'),
            'type' => $this->request->get('type'),
        ]);

        return $this->success(['tag' => $tag]);
    }

    public function update(int $tagId)
    {
        $this->authorize('update', Tag::class);

        $this->validate($this->request, [
            'name' => "string|min:2|unique:tags,name,$tagId",
            'display_name' => 'string|min:2',
            'type' => 'string|min:2',
        ]);

        $tag = $this->getModel()->findOrFail($tagId);

        $tag->fill($this->request->all())->save();

        return $this->success(['tag' => $tag]);
    }

    public function destroy(string $ids)
    {
        $tagIds = explode(',', $ids);
        $this->authorize('destroy', [Tag::class, $tagIds]);

        $this->getModel()->whereIn('id', $tagIds)->delete();
        DB::table('taggables')->whereIn('tag_id', $tagIds)->delete();

        return $this->success();
    }

    /**
     * @return Tag
     */
    protected function getModel()
    {
        return $tag = app(class_exists(AppTag::class) ? AppTag::class : Tag::class);
    }
}
