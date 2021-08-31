<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\Filters\Traits\NormalizesFiltersForFulltextEngines;

class MeilisearchFilter extends BaseFilter
{
    use NormalizesFiltersForFulltextEngines;

    public function apply()
    {
        $modelKeys = $this->query
            ->getModel()
            ->search($this->searchTerm, function (
                $driver,
                string $query,
                array $options
            ) {
                $filters = $this->coerceFilterValuesToString();
                $filters = implode(' AND ', $filters);
                if ($filters) {
                    $options['filters'] = $filters;
                }
                return $driver->search($query, $options);
            })
            ->keys()
            ->toArray();

        $table = $this->query->getModel()->getTable();
        $this->query->whereIn("$table.id", $modelKeys);
        return $this->query;
    }

    private function coerceFilterValuesToString(): array
    {
        return array_map(function ($filter) {
            $filter['value'] = $this->normalizeFilterValue($filter);
            $filter['operator'] = $this->normalizeFilterOperator($filter);
            return implode('', $filter);
        }, $this->filters->getAll());
    }
}
