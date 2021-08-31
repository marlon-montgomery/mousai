<?php namespace Common\Files\Controllers;

use Arr;
use Auth;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Common\Files\Actions\Deletion\DeleteEntries;
use Common\Files\Actions\UploadFile;
use Common\Files\FileEntry;
use Common\Files\Requests\UploadFileRequest;
use Common\Files\Response\FileResponseFactory;
use Common\Files\Traits\TransformsFileEntryResponse;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileEntriesController extends BaseController
{
    use TransformsFileEntryResponse;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var FileEntry
     */
    protected $entry;

    public function __construct(Request $request, FileEntry $entry)
    {
        $this->request = $request;
        $this->entry = $entry;
    }

    public function index()
    {
        $params = $this->request->all();
        $params['userId'] = $this->request->get('userId');

        $this->authorize('index', FileEntry::class);

        $dataSource = new MysqlDataSource(
            $this->entry->with(['users']),
            $params,
        );

        $pagination = $dataSource->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    public function show($id, FileResponseFactory $response)
    {
        // ID might be with extension: "4546.mp4" or as hash: "ja4d5ad4" or int: 4546
        $intId = (int) $id;
        if ($intId === 0) {
            $intId = $this->entry->decodeHash($id);
        }

        $entry = $this->entry->withTrashed()->findOrFail($intId);

        $this->authorize('show', $entry);

        try {
            return $response->create($entry);
        } catch (FileNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * @param UploadFileRequest $request
     * @return JsonResponse
     */
    public function store(UploadFileRequest $request)
    {
        $parentId = $request->get('parentId');
        $uploadedFile = $this->request->file('file');

        $this->authorize('store', [FileEntry::class, $parentId]);

        $params = $this->request->except('file');
        $fileEntry = app(UploadFile::class)->execute(
            Arr::get($params, 'disk', 'private'),
            $uploadedFile,
            $params,
        );

        return $this->success(
            $this->transformFileEntryResponse(
                ['fileEntry' => $fileEntry->load('users')],
                $params,
            ),
            201,
        );
    }

    /**
     * @param int $entryId
     * @return JsonResponse
     */
    public function update($entryId)
    {
        $this->authorize('update', [FileEntry::class, [$entryId]]);

        $this->validate($this->request, [
            'name' => 'string|min:3|max:200',
            'description' => 'nullable|string|min:3|max:200',
        ]);

        $params = $this->request->all();
        $entry = $this->entry->findOrFail($entryId);

        $entry->fill($params)->update();

        return $this->success(
            $this->transformFileEntryResponse(
                ['fileEntry' => $entry->load('users')],
                $params,
            ),
        );
    }

    public function destroy()
    {
        $entryIds = $this->request->get('entryIds');
        $userId = Auth::user()->id;

        $this->validate($this->request, [
            'entryIds' =>
                'requiredWithoutAll:emptyTrash,paths|array|exists:file_entries,id',
            'paths' => 'requiredWithoutAll:emptyTrash,entryIds|array',
            'deleteForever' => 'boolean',
            'emptyTrash' => 'boolean',
        ]);

        // get all soft deleted entries for user, if we are emptying trash
        if ($this->request->get('emptyTrash')) {
            $entryIds = $this->entry
                ->whereOwner($userId)
                ->onlyTrashed()
                ->pluck('id')
                ->toArray();
        }

        app(DeleteEntries::class)->execute([
            'paths' => $this->request->get('paths'),
            'entryIds' => $entryIds,
            'soft' =>
                !$this->request->get('deleteForever') &&
                !$this->request->get('emptyTrash'),
        ]);

        return $this->success();
    }
}
