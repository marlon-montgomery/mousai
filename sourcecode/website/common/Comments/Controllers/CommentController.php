<?php

namespace Common\Comments\Controllers;

use Common\Comments\Comment;
use Common\Comments\CrupdateComment;
use Common\Comments\CrupdateCommentRequest;
use Common\Core\BaseController;
use Common\Database\Datasource\Datasource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends BaseController
{
    /**
     * @var Comment
     */
    private $comment;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Comment $comment, Request $request)
    {
        $this->comment = $comment;
        $this->request = $request;
    }

    public function index(): Response
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [Comment::class, $userId]);

        $dataSource = new Datasource(
            $this->comment->with(['user']),
            $this->request->all(),
        );

        $pagination = $dataSource->paginate();

        $pagination->transform(function (Comment $comment) {
            if ($comment->relationLoaded('commentable')) {
                $normalized = $comment->commentable->toNormalizedArray();
                $comment->unsetRelation('commentable');
                $comment->setAttribute('commentable', $normalized);
            }
            return $comment;
        });

        return $this->success(['pagination' => $pagination]);
    }

    public function show(Comment $comment): Response
    {
        $this->authorize('show', $comment);

        return $this->success(['comment' => $comment]);
    }

    public function store(CrupdateCommentRequest $request): Response
    {
        $this->authorize('store', Comment::class);

        $comment = app(CrupdateComment::class)->execute($request->all());

        return $this->success(['comment' => $comment]);
    }

    public function update(
        Comment $comment,
        CrupdateCommentRequest $request
    ): Response {
        $this->authorize('store', $comment);

        $comment = app(CrupdateComment::class)->execute(
            $request->all(),
            $comment,
        );

        return $this->success(['comment' => $comment]);
    }

    public function destroy(string $ids): Response
    {
        $commentIds = explode(',', $ids);
        $this->authorize('destroy', [Comment::class, $commentIds]);

        $allDeleted = [];
        $allMarkedAsDeleted = [];

        $this->comment
            ->whereIn('id', $commentIds)
            ->chunkById(100, function (Collection $comments) use (
                &$allDeleted,
                &$allMarkedAsDeleted
            ) {
                $toMarkAsDeleted = [];
                $toDelete = [];
                foreach ($comments as $comment) {
                    if ($comment->allChildren()->count() > 1) {
                        $toMarkAsDeleted[] = $comment->id;
                    } else {
                        $toDelete[] = $comment->id;
                    }
                }
                if (!empty($toMarkAsDeleted)) {
                    $this->comment
                        ->whereIn('id', $toMarkAsDeleted)
                        ->update(['deleted' => true]);
                }
                if (!empty($toDelete)) {
                    $this->comment->whereIn('id', $toDelete)->delete();
                }
                $allDeleted = array_merge($allDeleted, $toDelete);
                $allMarkedAsDeleted = array_merge(
                    $allMarkedAsDeleted,
                    $toMarkAsDeleted,
                );
            });

        return $this->success([
            'allDeleted' => $allDeleted,
            'allMarkedAsDeleted' => $allMarkedAsDeleted,
        ]);
    }

    public function restore()
    {
        $this->authorize('update', Comment::class);

        $commentIds = $this->request->get('commentIds');

        $this->comment
            ->whereIn('id', $commentIds)
            ->update(['deleted' => false]);

        return $this->success();
    }
}
