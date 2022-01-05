<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\Filters\Traits\SupportsMysqlFilters;
use Laravel\Scout\Builder;

class TntFilterer extends BaseFilterer
{
    use SupportsMysqlFilters;

    public function apply(): ?Builder
    {
        $constrains = $this->applyMysqlFilters(
            $this->filters,
            $this->query->getModel()->newInstance(),
        );

        return $this->query
            ->getModel()
            ->search($this->searchTerm)
            ->constrain($constrains);
    }
}
