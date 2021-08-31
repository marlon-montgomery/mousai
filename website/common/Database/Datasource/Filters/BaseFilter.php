<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\DatasourceFilters;

abstract class BaseFilter
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
     * @var mixed
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

    abstract public function apply();
}
