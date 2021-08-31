<?php

namespace App\Services\Playlists;

use App\Playlist;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginatePlaylists
{
    public function execute(array $params, Builder $builder = null): LengthAwarePaginator
    {
        $builder = $builder ?? Playlist::query();
        $builder->with(['editors' => function (BelongsToMany $q) {
            return $q->compact();
        }]);

        $datasource = (new MysqlDataSource($builder, $params));
        $order = $datasource->getOrder();

        if ($order['col'] === 'popularity') {
            $datasource->order = false;
            $builder->orderByPopularity($order['dir']);
        }

        return $datasource->paginate();
    }
}
