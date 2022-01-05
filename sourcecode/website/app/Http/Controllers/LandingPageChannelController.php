<?php

namespace App\Http\Controllers;

use App\Channel;
use Common\Core\BaseController;
use Common\Settings\Settings;

class LandingPageChannelController extends BaseController
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Channel $channel, Settings $settings)
    {
        $this->channel = $channel;
        $this->settings = $settings;
    }

    public function index()
    {
        $channelIds = $this->settings->getJson('homepage.appearance')['channelIds'];
        $channels = $this->channel->whereIn('id', $channelIds)->get();
        $params = array_merge(request()->all(), ['perPage' => 10, 'simplePagination' => true]);


        $channels->transform(function(Channel $channel) use($params) {
            return $channel->loadContent($params);
        });

        $config = [
            'prerender.view' => 'home.show',
            'prerender.config' => 'home.show',
        ];

        return $this->success(['channels' => $channels], 200, $config);
    }
}
