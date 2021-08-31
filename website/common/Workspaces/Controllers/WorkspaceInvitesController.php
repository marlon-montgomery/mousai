<?php

namespace Common\Workspaces\Controllers;

use App\User;
use Arr;
use Auth;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Common\Workspaces\Notifications\WorkspaceInvitation;
use Common\Workspaces\Workspace;
use Common\Workspaces\WorkspaceInvite;
use Common\Workspaces\WorkspaceMember;
use Illuminate\Http\Request;
use Notification;
use Str;

class WorkspaceInvitesController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var WorkspaceInvite
     */
    private $workspaceInvite;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(
        Request $request,
        WorkspaceInvite $workspaceInvite,
        User $user,
        Settings $settings
    ) {
        $this->request = $request;
        $this->workspaceInvite = $workspaceInvite;
        $this->user = $user;
        $this->settings = $settings;
    }

    public function resend(Workspace $workspace, WorkspaceInvite $workspaceInvite)
    {
        $this->authorize('store', [WorkspaceMember::class, $workspace]);

        $notification = new WorkspaceInvitation($workspace, Auth::user()->display_name, $workspaceInvite['id']);
        Notification::send($workspaceInvite->user, $notification);
        $workspaceInvite->touch();

        return $this->success(['invite' => $workspaceInvite]);

    }

    public function store(Workspace $workspace)
    {
        $this->authorize('store', [WorkspaceMember::class, $workspace]);

        $validatedData = $this->request->validate([
            'emails.*' => 'required|email',
            'roleId' => 'required|int',
        ]);

        $invites = app(WorkspaceInvite::class)
            ->where('workspace_id', $workspace->id)
            ->whereIn('email', $validatedData['emails'])
            ->pluck('email');
        $alreadyInvitedEmails = app(WorkspaceMember::class)
            ->where('workspace_id', $workspace->id)
            ->join('users', 'users.id', 'workspace_user.user_id')
            ->where('users.email', $validatedData['emails'])
            ->pluck('email')
            ->merge($invites)
            ->toArray();

        $validatedData['emails'] = array_diff($validatedData['emails'], $alreadyInvitedEmails);

        if ( ! empty($validatedData['emails'])) {
            $existingUsers = $this->user->whereIn('email', $validatedData['emails'])->get()->keyBy('email');

            $workspaceInvites = collect($validatedData['emails'])->map(function($email) use($existingUsers, $validatedData, $workspace) {
                // if registration is disabled, only allow inviting already registered users
                if ($this->settings->get('disable.registration') && !isset($existingUsers[$email])) {
                    return null;
                }
                return [
                    'id' => Str::orderedUuid(),
                    'email' => $email,
                    'user_id' => $existingUsers[$email]['id'] ?? null,
                    'workspace_id' => $workspace->id,
                    'avatar' => isset($existingUsers[$email]) ? $existingUsers[$email]->getRawOriginal('avatar') ?? null : null,
                    'role_id' => $validatedData['roleId'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->filter();

            $this->workspaceInvite->insert($workspaceInvites->toArray());

            $workspaceInvites->each(function($invite) use($workspace, $existingUsers) {
                $notification = new WorkspaceInvitation($workspace, Auth::user()->display_name, $invite['id']);
                if ($user = Arr::get($existingUsers, $invite['email'])) {
                    Notification::send($user, $notification);
                } else {
                    Notification::route('mail', $invite['email'])->notify($notification);
                }
            });

            $invites = $workspace->invites()->whereIn('workspace_invites.id', $workspaceInvites->pluck('id'))->get();
        }

        return $this->success([
            'invites' => $invites ?? []
        ]);
    }

    public function destroy(WorkspaceInvite $workspaceInvite) {

        $workspace = Workspace::findOrFail($workspaceInvite->workspace_id);
        $this->authorize('destroy', [WorkspaceMember::class, $workspace, $workspaceInvite->user_id]);

        $workspaceInvite->delete();

        return $this->success();
    }

    public function changeRole(Workspace $workspace, string $inviteId)
    {
        $this->authorize('update', [WorkspaceMember::class, $workspace]);

        $validatedData = $this->request->validate([
            'roleId' => 'required|integer'
        ]);

        app(WorkspaceInvite::class)
            ->where('id', $inviteId)
            ->update(['role_id' => $validatedData['roleId']]);
    }
}
