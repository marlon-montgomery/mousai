<?php

namespace App\Actions\Backstage;

use Auth;
use App\BackstageRequest;

class CrupdateBackstageRequest
{
    /**
     * @var BackstageRequest
     */
    private $backstageRequest;

    /**
     * @param BackstageRequest $backstageRequest
     */
    public function __construct(BackstageRequest $backstageRequest)
    {
        $this->backstageRequest = $backstageRequest;
    }

    /**
     * @param array $data
     * @param BackstageRequest $backstageRequest
     * @return BackstageRequest
     */
    public function execute($data, $backstageRequest = null)
    {
        if ( ! $backstageRequest) {
            $backstageRequest = $this->backstageRequest->newInstance([
                'user_id' => Auth::id(),
            ]);
        }

        if ( ! isset($data['artist_id'])) {
            $artist = Auth::user()->primaryArtist();
            $data['artist_id'] = $artist->id ?? null;
        }

        $attributes = [
            'artist_name' => $data['artist_name'],
            'artist_id' => $data['artist_id'],
            'type' => $data['type'],
            'data' => json_encode($data['data']),
        ];

        $backstageRequest->fill($attributes)->save();

        return $backstageRequest;
    }
}
