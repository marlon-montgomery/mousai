<?php

namespace Common\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginatedResourceCollection extends AnonymousResourceCollection
{
    /**
     * @var LengthAwarePaginator
     */
    public $resource;

    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'current_page' => $this->resource->currentPage(),
            'data' => $this->collection,
            'from' => $this->resource->firstItem(),
            'last_page' => $this->resource->lastPage(),
            'per_page' => $this->resource->perPage(),
            'to' => $this->resource->lastItem(),
            'total' => $this->total(),
        ];
    }
}
