<?php

namespace Common\Workspaces\Listeners;

use Common\Workspaces\Actions\JoinWorkspace;
use Common\Workspaces\WorkspaceInvite;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;

class AttachWorkspaceToUser
{
    /**
     * @param  Login|Registered  $event
     * @return void
     */
    public function handle($event)
    {
        $inviteId = session()->get('workspaceInvite');
        if ( ! $inviteId) return;

        $invite = app(WorkspaceInvite::class)->find($inviteId);
        if ($invite) {
            app(JoinWorkspace::class)->execute($invite, $event->user);
        }
    }
}
