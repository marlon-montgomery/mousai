<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\Filters\Traits\SupportsMysqlFilters;
use Laravel\Scout\Builder;

class MysqlFilterer extends BaseFilterer
{
    use SupportsMysqlFilters;

    public function apply(): ?Builder
    {
        $this->applyMysqlFilters($this->filters, $this->query);

        if ($this->searchTerm) {
            $this->query->mysqlSearch($this->searchTerm);
        }

        return null;
    }
}
