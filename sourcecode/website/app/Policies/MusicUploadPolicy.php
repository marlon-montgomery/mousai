<?php

namespace App\Policies;

use App\User;
use Common\Core\Policies\FileEntryPolicy;

class MusicUploadPolicy extends FileEntryPolicy
{
    public function store(User $user, int $parentId = null): bool
    {
        if (request('diskPrefix') === 'track_media' && $user->hasPermission('music.create')) {
            return true;
        } else {
            return parent::store($user, $parentId);
        }
    }
}
