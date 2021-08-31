<?php

namespace Common\Workspaces\Actions;

use Auth;
use Common\Workspaces\Workspace;

class CrupdateWorkspace
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @param Workspace $workspace
     */
    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @param array $data
     * @param Workspace $initialWorkspace
     * @return Workspace
     */
    public function execute($data, $initialWorkspace = null)
    {
        if ($initialWorkspace) {
            $workspace = $initialWorkspace;
        } else {
            $workspace = $this->workspace->newInstance([
                'owner_id' => Auth::id(),
            ]);
        }

        $attributes = [
            'name' => $data['name'],
        ];

        $workspace->fill($attributes)->save();

        if ( ! $initialWorkspace) {
            $workspace->members()->create(['user_id' => Auth::id(), 'is_owner' => true]);
        }

        return $workspace;
    }
}
