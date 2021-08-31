<?php

namespace Common\Workspaces\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    public $workspaceId;

    /**
     * @var int
     */
    public $ownerId;

    public function __construct(int $workspaceId, int $ownerId)
    {
        $this->workspaceId = $workspaceId;
        $this->ownerId = $ownerId;
    }
}
