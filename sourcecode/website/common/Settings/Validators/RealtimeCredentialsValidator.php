<?php

namespace Common\Settings\Validators;

use Config;
use Exception;
use Arr;
use Pusher\Pusher;

class RealtimeCredentialsValidator
{
    const KEYS = ['pusher_key', 'pusher_secret', 'pusher_app_id', 'pusher_cluster'];

    public function fails($settings)
    {
        $this->setConfigDynamically($settings);

       try {
           $config = Config::get('broadcasting.connections.pusher');
           $pusher = new Pusher($config['key'], $config['secret'],
               $config['app_id'], Arr::get($config, 'options', []));
           if ($pusher->get_channels() === false) {
               return $this->getErrorMessage();
           }
       } catch (Exception $e) {
           return $this->getErrorMessage();
       }
    }

    private function setConfigDynamically($settings)
    {
        foreach (self::KEYS as $key) {
            if ( ! Arr::has($settings, $key)) continue;
            if ($key === 'pusher_cluster') {
                Config::set("broadcasting.connections.pusher.options.cluster", $settings[$key]);
            } else {
                $configKey = str_replace('pusher_', '', $key);
                Config::set("broadcasting.connections.pusher.$configKey", $settings[$key]);
            }
        }
    }

    /**
     * @param Exception $e
     * @return array
     */
    private function getErrorMessage($e = null)
    {
        return ['pusher_group' => 'These pusher credentials are not valid.'];
    }
}
