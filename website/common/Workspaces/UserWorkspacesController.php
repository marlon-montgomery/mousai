<?php

namespace Common\Workspaces;

use Auth;
use Common\Core\BaseController;

class UserWorkspacesController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $workspaces = Workspace::forUser(Auth::id())
            ->with(['members'])
            ->limit(20)
            ->get()
            ->map(function (Workspace $workspace) {
                $workspace->members_count = $workspace->members->count();
                $workspace->setCurrentUserAndOwner();
                return $workspace;
            });

        return $this->success([
            'workspaces' => $workspaces,
        ]);
    }
}
