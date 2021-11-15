<?php

namespace Common\Workspaces\Controllers;

use App\User;
use Auth;
use Common\Core\BaseController;
use Common\Workspaces\Actions\JoinWorkspace;
use Common\Workspaces\Actions\RemoveMemberFromWorkspace;
use Common\Workspaces\Workspace;
use Common\Workspaces\WorkspaceInvite;
use Common\Workspaces\WorkspaceMember;
use Illuminate\Http\Request;
use Session;
use const App\Providers\WORKSPACE_HOME_ROUTE;

class WorkspaceMembersController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    public function __construct(
        Request $request,
        User $user
    ) {
        $this->request = $request;
        $this->user = $user;
    }

    public function join(WorkspaceInvite $workspaceInvite)
    {
        if ($user = Auth::user()) {
            app(JoinWorkspace::class)->execute($workspaceInvite, $user);
            if ($this->request->expectsJson()) {
                return $this->success(['workspace' => $workspaceInvite->workspace->loadCount('members')]);
            } else {
                return redirect(WORKSPACE_HOME_ROUTE);
            }
        } else {
            Session::put('workspaceInvite', $workspaceInvite->id);
            if (User::where('email', $workspaceInvite->email)->exists()) {
                return redirect("workspace/join/login?email={$workspaceInvite->email}");
            } else {
                return redirect("workspace/join/register?email={$workspaceInvite->email}");
            }
        }
    }

    public function destroy(Workspace $workspace, int $userId) {

        $this->authorize('destroy', [WorkspaceMember::class, $workspace, $userId]);

        app(RemoveMemberFromWorkspace::class)->execute($workspace, $userId);

        return $this->success();
    }

    public function changeRole(Workspace $workspace, int $memberId)
    {
        $this->authorize('update', [WorkspaceMember::class, $workspace]);

        $validatedData = $this->request->validate([
            'roleId' => 'required|integer'
        ]);

        app(WorkspaceMember::class)
            ->where('id', $memberId)
            ->update(['role_id' => $validatedData['roleId']]);

        return $this->success();
    }
}
