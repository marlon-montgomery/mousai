<?php

namespace App\Services\Tracks\Queries;

use App\Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

abstract class BaseTrackQuery
{
    const ORDER_DIR = 'desc';

    /**
     * @var array
     */
    private $params;

    /**
     * @param array $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @param int $modelId
     * @return Builder
     */
    abstract public function get($modelId);

    /**
     * @return Builder
     */
    protected function baseQuery()
    {
        $order = $this->getOrder();

        return app(Track::class)
            ->with(['artists', 'album' => function(BelongsTo $q) {
                return $q->select('id', 'name', 'image');
            }])
            ->orderBy($order['col'], $order['dir'])
            ->orderBy('tracks.id', 'desc');
    }

    public function getOrder()
    {
        return [
            'col' => Arr::get($this->params, 'orderBy') ?: static::ORDER_COL,
            'dir' => Arr::get($this->params, 'orderDir') ?: static::ORDER_DIR,
        ];
    }
}
