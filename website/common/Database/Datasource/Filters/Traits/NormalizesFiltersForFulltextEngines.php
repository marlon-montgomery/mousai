<?php

namespace Common\Database\Datasource\Filters\Traits;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait NormalizesFiltersForFulltextEngines
{
    protected function normalizeFilterValue(array $filter): string
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
        } else if (array_search($filter['key'], $this->query->getModel()->getDates()) !== false) {
            return Carbon::parse($value)->timestamp;
        } else {
            return $value;
        }
    }

    protected function normalizeFilterOperator(array $filter): string
    {
        if ($filter['operator'] === 'has') {
            return '=';
        } else if ($filter['operator'] === 'doesntHave') {
            return  '!=';
        } else {
            return $filter['operator'];
        }
    }
}
