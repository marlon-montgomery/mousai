<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\Filters\Traits\SupportsMysqlFilters;

class MysqlFilter extends BaseFilter
{
    use SupportsMysqlFilters;

    public function apply()
    {
        $this->applyMysqlFilters($this->filters, $this->query);

        if ($this->searchTerm) {
            $this->query->mysqlSearch($this->searchTerm);
        }

        return $this->query;
    }
}
