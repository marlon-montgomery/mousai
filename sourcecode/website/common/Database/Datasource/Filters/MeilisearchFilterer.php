<?php

namespace Common\Database\Datasource\Filters;

use Common\Database\Datasource\Filters\Traits\NormalizesFiltersForFulltextEngines;
use Laravel\Scout\Builder;

class MeilisearchFilterer extends BaseFilterer
{
    use NormalizesFiltersForFulltextEngines;

    public function apply(): Builder
    {
        return $this->query
            ->getModel()
            ->search($this->searchTerm, function (
                $driver,
                ?string $query,
                array $options
            ) {
                $filters = $this->coerceFilterValuesToString();
                $filters = implode(' AND ', $filters);
                if ($filters) {
                    $options['filter'] = $filters;
                }
                return $driver->search($query, $options);
            });
    }

    private function coerceFilterValuesToString(): array
    {
        return array_map(function ($filter) {
            $operator = $this->normalizeFilterOperator($filter);
            $key = $filter['key'];
            $value = $this->normalizeFilterValue($filter);
            if (is_array($value)) {
                $values = array_map(function ($v) use ($key, $operator) {
                    return $this->createFilterString($key, $operator, $v);
                }, $value);
                return '(' . implode(' OR ', $values) . ')';
            } else {
                return $this->createFilterString($key, $operator, $value);
            }
        }, $this->filters->getAll());
    }

    private function createFilterString(
        string $key,
        string $operator,
        $value
    ): string {
        return "$key $operator $value";
    }
}
