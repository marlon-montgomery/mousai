<?php

namespace Common\Database\Datasource;

use Illuminate\Database\Eloquent\Model;

class DatasourceFilters
{
    /**
     * @var array
     */
    private $filters;

    /**
     * @var Model
     */
    private $model;

    public function __construct(?string $encodedFilters, Model $model)
    {
        $this->model = $model;
        $this->filters = $this->decodeFilters($encodedFilters);
    }

    public function getAll(): array
    {
        return $this->filters;
    }

    public function empty(): bool
    {
        return empty($this->filters);
    }

    public function where(string $key, string $operator, $value): self
    {
        $this->filters[] = [
            'key' => $key,
            'operator' => $operator,
            'value' => $value,
        ];
        return $this;
    }

    public function getAndRemove(
        string $key,
        string $operator = null,
        $value = null
    ): ?array {
        // use func_get_args as "null" is a valid value, need
        // to check whether if it was actually passed by user
        $args = func_get_args();

        foreach ($this->filters as $key => $filter) {
            if (
                $filter['key'] === $args[0] &&
                (!isset($args[1]) || $filter['operator'] === $args[1]) &&
                (!isset($args[2]) || $filter['value'] === $args[2])
            ) {
                unset($this->filters[$key]);
                return $filter;
            }
        }

        return null;
    }

    private function decodeFilters(?string $filterString): array
    {
        if ($filterString) {
            $filters = json_decode(
                base64_decode(urldecode($filterString)),
                true,
            );
            return collect($filters)
                ->map(function ($filter) {
                    return $this->normalizeFilter($filter);
                })
                ->filter()
                ->toArray();
        } else {
            return [];
        }
    }

    private function normalizeFilter(array $filter): ?array
    {
        $value = $filter['value'];
        $operator = $filter['operator'] ?? '=';
        if (is_array($value)) {
            // filtering by normalized model
            if (isset($value['id'])) {
                $value = $value['id'];

                // "value" contains both value and operator
            } elseif (array_key_exists('value', $value)) {
                $operator = $value['operator'] ?? $operator;
                $value = $value['value'];
            }
        }

        return [
            'key' => $filter['key'],
            'operator' => $operator,
            'value' => $value,
        ];
    }
}
