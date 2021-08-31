<?php

namespace Common\Plays;

use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LogModelPlay
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Agent
     */
    private $agent;

    /**
     * @param Request $request
     * @param Agent $agent
     */
    public function __construct(Request $request, Agent $agent)
    {
        $this->request = $request;
        $this->agent = $agent;
    }

    /**
     * @param Model $model
     * @return Model|void
     */
    public function execute(Model $model)
    {
        // only log play every 30 seconds for same track
        $existing = $model->plays()
            ->whereBetween('created_at', [Carbon::now()->subMinute(), Carbon::now()])
            ->first();
        if ( ! $existing) {
            return $this->log($model);
        }
    }

    /**
     * @param Model $model
     * @return Model
     */
    private function log(Model $model)
    {
        $attributes = [
            'location' => $this->getLocation(),
            'platform' => strtolower($this->agent->platform()),
            'device' => $this->getDevice(),
            'browser' => strtolower($this->agent->browser()),
            'user_id' => Auth::id(),
        ];

        return $model->plays()->create($attributes);
    }

    private function getDevice() {
        if ($this->agent->isMobile()) {
            return 'mobile';
        } else if ($this->agent->isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    private function getLocation()
    {
        return strtolower(geoip($this->getIp())['iso_code']);
    }

    private function getIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return $this->request->ip();
    }
}
