<?php

namespace App\Services\Tracks;

use App\Genre;
use App\Track;
use Common\Database\Datasource\Datasource;

class PaginateTracks
{
    public function execute(array $params, Genre $genre = null)
    {
        if ($genre) {
            $builder = $genre->tracks();
        } else {
            $builder = Track::query();
        }

        $builder
            ->with('album')
            ->with('artists')
            ->withCount('plays');

        $datasource = (new Datasource($builder, $params));
        $order = $datasource->getOrder();

        if ($order['col'] === 'popularity') {
            $datasource->order = false;
            $builder->orderByPopularity($order['dir']);
        }

        return $datasource->paginate();
    }
}
