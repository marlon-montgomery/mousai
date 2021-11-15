<?php

namespace App\Policies;

use App\Channel;
use App\User;
use Common\Core\Policies\BasePolicy;

class ChannelPolicy extends BasePolicy
{
    public function index(?User $user, $userId = null)
    {
        return $this->userOrGuestHasPermission($user, 'channels.view') || $user->id === (int) $userId;
    }

    public function show(?User $user, Channel $channel)
    {
        return $this->userOrGuestHasPermission($user, 'channels.view') || $this->userOrGuestHasPermission($user, 'music.view') || $channel->user_id === $user->id;
    }

    public function store(User $user)
    {
        return $user->hasPermission('channels.create');
    }

    public function update(User $user, Channel $channel)
    {
        return $user->hasPermission('channels.update') || $channel->user_id === $user->id;
    }

    public function destroy(User $user, $channelIds)
    {
        if ($user->hasPermission('channels.delete')) {
            return true;
        } else {
            $dbCount = app(Channel::class)
                ->whereIn('id', $channelIds)
                ->where('user_id', $user->id)
                ->count();
            return $dbCount === count($channelIds);
        }
    }
}
