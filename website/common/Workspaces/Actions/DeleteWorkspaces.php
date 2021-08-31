<?php

namespace Common\Workspaces\Actions;

use Common\Workspaces\Events\WorkspaceDeleted;
use Common\Workspaces\Workspace;
use Common\Workspaces\WorkspaceMember;

class DeleteWorkspaces
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function execute($ids)
    {
        $workspaces = $this->workspace->whereIn('id', $ids)->get();

        $workspaces->each(function(Workspace $workspace) {
            $workspace->invites()->delete();
            $workspace->members->each(function (WorkspaceMember $member) use($workspace) {
                app(RemoveMemberFromWorkspace::class)->execute($workspace, $member->id);
            });
            event(new WorkspaceDeleted($workspace->id, $workspace->owner_id));
        });

        $this->workspace->whereIn('id', $ids)->delete();
    }

}
