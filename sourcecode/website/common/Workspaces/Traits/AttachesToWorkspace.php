<?php

namespace Common\Workspaces\Traits;

use Common\Workspaces\ActiveWorkspace;
use Illuminate\Database\Eloquent\Model;

trait AttachesToWorkspace
{
    protected static function booted()
    {
        static::creating(function (Model $builder) {
            $activeWorkspace = app(ActiveWorkspace::class);
            $builder->workspace_id = $activeWorkspace->id ?? null;
        });
    }
}
