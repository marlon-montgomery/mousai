<?php

namespace Common\Workspaces;

use App\User;
use Arr;
use Auth;

class ActiveWorkspace
{
    const HEADER = 'Be-Workspace-Id';

    /**
     * @var Workspace|null;
     */
    private $cachedWorkspace;
    private $memberCache = [];
    public $id;

    public function __construct()
    {
        $this->id = (int) Arr::get($_COOKIE, $this->cookieName()) ?: null;
    }

    public function workspace(): ?Workspace
    {
        if (is_null($this->cachedWorkspace)) {
            $workspaceId = $this->id;
            $this->cachedWorkspace = $this->personal()
                ? 0
                : Workspace::find($workspaceId) ?? 0;
            if (!$this->cachedWorkspace) {
                cookie()->queue(
                    $this->cookieName(),
                    null,
                    -2628000,
                    null,
                    null,
                    null,
                    false,
                );
            }
        }

        return $this->cachedWorkspace ?: null;
    }

    public function personal(): bool
    {
        return !$this->id;
    }

    public function owner(): User
    {
        return $this->workspace()->owner_id === Auth::id()
            ? Auth::user()
            : $this->workspace()->owner;
    }

    public function currentUserIsOwner(): bool
    {
        if ($this->personal()) {
            return true;
        }
        return $this->workspace() && $this->workspace()->owner_id === Auth::id();
    }

    public function member(int $userId): ?WorkspaceMember
    {
        if (!isset($this->memberCache[$userId])) {
            $this->memberCache[$userId] = app(WorkspaceMember::class)
                ->where([
                    'user_id' => $userId,
                    'workspace_id' => $this->workspace()->id,
                ])
                ->first();
        }
        return $this->memberCache[$userId];
    }

    private function cookieName(): string
    {
        $userId = Auth::id();
        return "{$userId}_activeWorkspace";
    }
}
