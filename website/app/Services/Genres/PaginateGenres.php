<?php

namespace App\Services\Genres;

use App\Genre;
use Common\Database\Datasource\MysqlDataSource;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginateGenres
{
    public function execute(array $params): LengthAwarePaginator
    {
        $datasource = (new MysqlDataSource(Genre::query(), $params));

        return $datasource->paginate();
    }
}
