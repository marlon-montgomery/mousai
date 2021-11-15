<?php

namespace Common\Comments\Controllers;

use Common\Comments\PaginateModelComments;
use Common\Core\BaseController;

class CommentableController extends BaseController
{
    public function loadComments()
    {
        $modelType = request('commentableType');
        $modelId = request('commentableId');

        if (!$modelType || !$modelId) {
            abort(404);
        }

        $commentable = app(modelTypeToNamespace($modelType))->findOrFail(
            $modelId,
        );

        $pagination = app(PaginateModelComments::class)->execute($commentable);

        return $this->success([
            'pagination' => $pagination,
            'commentCount' => count($pagination['data']),
        ]);
    }
}
