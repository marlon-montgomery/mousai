<?php

namespace Common\Files\Controllers;

use Common\Files\Actions\Deletion\RestoreEntries;
use Common\Files\FileEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class RestoreDeletedEntriesController extends BaseController
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
    }

    /**
     * @param RestoreEntries $action
     * @return JsonResponse
     */
    public function restore(RestoreEntries $action)
    {
        $this->validate($this->request, [
            'entryIds' => 'required|array|exists:file_entries,id',
        ]);

        $entryIds = $this->request->get('entryIds');

        $this->authorize('destroy', [FileEntry::class, $entryIds]);

        $action->execute($entryIds);

        return $this->success();
    }
}
