<?php

namespace App\Listeners;

use App\Channel;
use Common\Admin\Appearance\AppearanceSaver;
use Common\Admin\Appearance\Events\AppearanceSettingSaved;
use Str;

class UpdateChannelSeoFields
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Handle the event.
     *
     * @param  AppearanceSettingSaved  $event
     * @return void
     */
    public function handle(AppearanceSettingSaved $event)
    {
        if ($event->type === AppearanceSaver::ENV_SETTING && $event->key === 'app_name') {
            $oldConfig = $this->channel->config;
            if (isset($oldConfig['seoTitle'])) {
                $oldConfig['seoTitle'] = Str::replace($event->previousValue, $event->value, $oldConfig['seoTitle']);
            }
        }
    }
}
