<?php

namespace App\Services\Albums;

use App\Album;
use App\Genre;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginateAlbums
{
    public function execute(array $params, Genre $genre = null): LengthAwarePaginator
    {
        if ($genre) {
            $builder = $genre
                ->albums()
                ->whereNotNull('image');
        } else {
            $builder = Album::query();
        }

        $builder->with(['artists']);

        $datasource = (new MysqlDataSource($builder, $params));
        $order = $datasource->getOrder();

        if ($order['col'] === 'popularity') {
            $datasource->order = false;
            $builder->orderByPopularity($order['dir']);
        }

        return $datasource->paginate();
    }
}
