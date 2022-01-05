<?php

namespace Common\Workspaces\Actions;

use Common\Workspaces\Workspace;
use Common\Workspaces\WorkspaceMember;
use const App\Providers\WORKSPACED_RESOURCES;

class RemoveMemberFromWorkspace
{
    public function execute(Workspace $workspace, int $userToBeRemoved)
    {
        // transfer workspace resources to owner
        if ($workspace->owner_id !== $userToBeRemoved) {
            foreach (WORKSPACED_RESOURCES as $model) {
                $baseName = class_basename($model);
                $namespace = "App\Workspaces\Transfer{$baseName}";
                if (class_exists($namespace)) {
                    app($namespace)->execute($workspace->id, $workspace->owner_id, $userToBeRemoved);
                } else {
                    app($model)
                        ->where('workspace_id', $workspace->id)
                        ->where('user_id', $userToBeRemoved)
                        ->update(['user_id' => $workspace->owner_id]);
                }

            }
        }

        app(WorkspaceMember::class)
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $userToBeRemoved)
            ->delete();
    }
}
