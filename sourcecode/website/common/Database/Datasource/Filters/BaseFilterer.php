<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\DatasourceFilters;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;

abstract class BaseFilterer
{
    /**
     * @var DatasourceFilters
     */
    protected $filters;

    /**
     * @var string
     */
    protected $searchTerm;

    /**
     * @var Builder
     */
    protected $query;

    public function __construct(
        $query,
        DatasourceFilters $filters,
        string $searchTerm = null
    ) {
        $this->filters = $filters;
        $this->query = $query;
        $this->searchTerm = $searchTerm;
    }

    abstract public function apply(): ?ScoutBuilder;
}
