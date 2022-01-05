<?php

namespace Common\Database\Datasource\Filters\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait NormalizesFiltersForFulltextEngines
{
    /**
     * @return string|string[]
     */
    protected function normalizeFilterValue(array $filter)
    {
        $value = $filter['value'];
        if (is_string($value) && Str::contains($value, ' ')) {
            return "'$value'";
        } elseif ($value === null) {
            return '_null';
        } elseif ($value === false) {
            return 'false';
        } elseif ($value === true) {
            return 'true';
        } elseif (
            array_search(
                $filter['key'],
                $this->query->getModel()->getDates(),
            ) !== false
        ) {
            return Carbon::parse($value)->timestamp;
        } else {
            return $value;
        }
    }

    protected function normalizeFilterOperator(array $filter): string
    {
        if ($filter['operator'] === 'has') {
            return '=';
        } elseif ($filter['operator'] === 'doesntHave') {
            return '!=';
        } else {
            return $filter['operator'];
        }
    }
}
