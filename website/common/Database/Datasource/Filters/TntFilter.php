<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\Filters\Traits\SupportsMysqlFilters;

class TntFilter extends BaseFilter
{
    use SupportsMysqlFilters;

    public function apply()
    {
        $constrains = $this->applyMysqlFilters(
            $this->filters,
            $this->query->getModel()->newInstance(),
        );

        $modelKeys = $this->query
            ->getModel()
            ->search($this->searchTerm)
            ->constrain($constrains)
            ->get()
            ->pluck('id')
            ->toArray();

        $this->query->whereIn(
            $this->query->getModel()->qualifyColumn('id'),
            $modelKeys,
        );
        return $this->query;
    }
}
