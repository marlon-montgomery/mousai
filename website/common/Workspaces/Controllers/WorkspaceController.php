<?php

namespace Common\Workspaces\Controllers;

use Auth;
use Common\Core\BaseController;
use Common\Database\Datasource\MysqlDataSource;
use Common\Workspaces\Actions\CrupdateWorkspace;
use Common\Workspaces\Actions\DeleteWorkspaces;
use Common\Workspaces\Requests\CrupdateWorkspaceRequest;
use Common\Workspaces\Workspace;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class WorkspaceController extends BaseController
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Workspace $workspace, Request $request)
    {
        $this->workspace = $workspace;
        $this->request = $request;
    }

    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [Workspace::class, $userId]);

        $builder = $this->workspace
            ->newQuery()
            ->withCount(['members'])
            ->with([
                'members' => function (HasMany $builder) {
                    $builder->with('permissions')->currentUserAndOwnerOnly();
                },
            ]);
        if ($userId) {
            $builder->forUser($userId);
        }

        $dataSource = new MysqlDataSource($builder, $this->request->all());

        $pagination = $dataSource->paginate();

        $pagination->transform(function (Workspace $workspace) {
            return $workspace->setCurrentUserAndOwner();
        });

        return $this->success(['pagination' => $pagination]);
    }

    public function show(Workspace $workspace)
    {
        $this->authorize('show', $workspace);

        $workspace->load(['invites', 'members']);

        if (
            $workspace->currentUser = $workspace->members
                ->where('id', Auth::id())
                ->first()
        ) {
            $workspace->currentUser->load('permissions');
        }

        return $this->success(['workspace' => $workspace]);
    }

    public function store(CrupdateWorkspaceRequest $request)
    {
        $this->authorize('store', Workspace::class);

        $workspace = app(CrupdateWorkspace::class)->execute($request->all());
        $workspace->loadCount('members');
        $workspace
            ->load([
                'members' => function (HasMany $builder) {
                    $builder->currentUserAndOwnerOnly();
                },
            ])
            ->setCurrentUserAndOwner();

        return $this->success(['workspace' => $workspace]);
    }

    public function update(
        Workspace $workspace,
        CrupdateWorkspaceRequest $request
    ) {
        $this->authorize('store', $workspace);

        $workspace = app(CrupdateWorkspace::class)->execute(
            $request->all(),
            $workspace,
        );

        return $this->success(['workspace' => $workspace]);
    }

    public function destroy(string $ids)
    {
        $workspaceIds = explode(',', $ids);
        $this->authorize('destroy', [Workspace::class, $workspaceIds]);

        app(DeleteWorkspaces::class)->execute($workspaceIds);

        return $this->success();
    }
}
