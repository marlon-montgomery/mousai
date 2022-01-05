<?php

namespace App\Actions\Channel;

use App\Channel;
use Illuminate\Support\Collection;

class LoadChannelMenuItems
{
    /**
     * @return Collection
     */
    public function execute()
    {
        return app(Channel::class)->limit(20)->get()
            ->map(function(Channel $channel) {
                return [
                    'label' => $channel->name,
                    'action' => "channels/{$channel->slug}",
                    'type' => 'route',
                    'model_id' => $channel->id
                ];
            });
    }
}