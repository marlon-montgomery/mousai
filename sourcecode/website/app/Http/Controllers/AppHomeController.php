<?php

namespace App\Http\Controllers;

use App\Channel;
use Common\Core\Controllers\HomeController;
use Common\Settings\Settings;

class AppHomeController extends HomeController
{
    protected function handleSeo(&$data = [], $options = [])
    {
        if(request()->method() === 'GET' && defined('SHOULD_PRERENDER')) {
            $settings = app(Settings::class);
            if ($settings->get('homepage.type') === 'Channel' && $channel = Channel::find($settings->get('homepage.value'))) {
                $options['prerender.config'] = 'channel.show';
                $options['prerender.view'] = 'channel.show';
                $data['channel'] = $channel->loadContent()->toArray();
            } else {
                $options['prerender.view'] = 'home.show';
                $options['prerender.config'] = 'home.show';
            }
        }

        return parent::handleSeo($data, $options);
    }

}
