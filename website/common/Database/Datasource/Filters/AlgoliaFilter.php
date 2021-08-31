<?php

namespace Common\Database\Datasource\Filters;

use Algolia\AlgoliaSearch\SearchIndex;
use Common\Database\Datasource\Filters\Traits\NormalizesFiltersForFulltextEngines;
use Illuminate\Support\Str;

class AlgoliaFilter extends BaseFilter
{
    use NormalizesFiltersForFulltextEngines;

    public function apply()
    {
        $modelKeys = $this->query
            ->getModel()
            ->search($this->searchTerm, function (SearchIndex $algolia, string $query, array $options) {
                $filters = $this->coerceFilterValuesToString();
                $filters = implode(' AND ', $filters);
                if ($filters) {
                    $options['filters'] = $filters;
                }
                return $algolia->search($query, $options);
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
            $prefix = '';

            $filter['value'] = $this->normalizeFilterValue($filter);
            $filter['operator'] = $this->normalizeFilterOperator($filter);

            if (Str::contains($filter['operator'], '!')) {
                $filter['operator'] = str_replace('!', '', $filter['operator']);
                $prefix = 'NOT ';
            }

            if ( ! is_numeric($filter['value']) && $filter['operator'] === '=') {
                $filter['operator'] = ':';
            }

            return $prefix . implode('', $filter);
        }, $this->filters);
    }
}
