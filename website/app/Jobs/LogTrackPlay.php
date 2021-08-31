<?php

namespace App\Jobs;

use App\Album;
use App\Artist;
use App\Playlist;
use App\Track;
use App\TrackPlay;
use Auth;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Jenssegers\Agent\Agent;

class LogTrackPlay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Track
     */
    protected $track;

    /**
     * @var string|null
     */
    protected $queueId;

    public function __construct(Track $track, ?string $queueId)
    {
        $this->track = $track;
        $this->queueId = $queueId;
    }

    public function handle(): ?TrackPlay
    {
        // only log play every minute for same track
        $existing = $this->track->plays()
            ->whereBetween('created_at', [Carbon::now()->subMinute(), Carbon::now()])
            ->first();
        if ( ! $existing) {
            return $this->log();
        }
        return null;
    }

    private function log(): TrackPlay
    {
        $agent = app(Agent::class);
        $attributes = [
            'location' => $this->getLocation(),
            'platform' => strtolower($agent->platform()),
            'device' => $this->getDevice(),
            'browser' => strtolower($agent->browser()),
            'user_id' => Auth::id(),
        ];

        $trackPlay = $this->track->plays()->create($attributes);

        Track::where('id', $this->track->id)->increment('plays');
        if ($this->track->album_id) {
            Album::where('id', $this->track->album_id)->increment('plays');
        }
        $artistIds = $this->track->artists->pluck('id');
        if ($artistIds->isNotEmpty()) {
            Artist::whereIn('id', $artistIds)->increment('plays');
        }

        list($modelType, $modelId) = array_pad(explode('.', $this->queueId), 2, null);
        if ($modelType === Playlist::MODEL_TYPE) {
            Playlist::where('id', $modelId)->increment('plays');
        }

        return $trackPlay;
    }

    private function getDevice(): string {
        $agent = app(Agent::class);
        if ($agent->isMobile()) {
            return 'mobile';
        } else if ($agent->isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    private function getLocation(): string
    {
        return strtolower(geoip($this->getIp())['iso_code']);
    }

    private function getIp() {
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
        return request()->ip();
    }
}
