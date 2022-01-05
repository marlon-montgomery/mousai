<?php

namespace App\Services\Genres;

use App\Genre;
use Common\Database\Datasource\Datasource;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginateGenres
{
    public function execute(array $params): LengthAwarePaginator
    {
        $datasource = (new Datasource(Genre::query(), $params));

        return $datasource->paginate();
    }
}
