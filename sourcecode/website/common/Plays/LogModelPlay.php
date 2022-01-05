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
     * @var Request
     */
    private $request;

    /**
     * @var Agent
     */
    private $agent;

    public function __construct(Request $request, Agent $agent)
    {
        $this->request = $request;
        $this->agent = $agent;
    }

    public function execute(Model $model)
    {
        if (!$this->alreadyLoggedInTheLastMinute($model)) {
            return $this->log($model);
        }
    }

    protected function alreadyLoggedInTheLastMinute(Model $model): bool
    {
        return $model
            ->plays()
            ->forCurrentUser()
            ->whereBetween('created_at', [
                Carbon::now()->subMinute(),
                Carbon::now(),
            ])
            ->exists();
    }

    protected function log(Model $model)
    {
        return $model->plays()->create($this->getAnalyticProps());
    }

    protected function getAnalyticProps(): array
    {
        $ip = getIp();
        return [
            'location' => $this->getLocation($ip),
            'platform' => strtolower($this->agent->platform()),
            'device' => $this->getDevice(),
            'browser' => strtolower($this->agent->browser()),
            'user_id' => Auth::id(),
            'ip' => $ip,
        ];
    }

    protected function getDevice(): string
    {
        if ($this->agent->isMobile()) {
            return 'mobile';
        } elseif ($this->agent->isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    protected function getLocation(string $ip): string
    {
        return strtolower(geoip($ip)['iso_code']);
    }
}
