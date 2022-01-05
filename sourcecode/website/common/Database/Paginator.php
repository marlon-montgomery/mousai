<?php namespace Common\Database;

use Cache;
use Carbon\Carbon;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Paginator
{
    /**
     * @var Builder
     */
    private $query;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var string
     */
    private $defaultOrderColumn = 'updated_at';

    /**
     * @var string
     */
    private $defaultOrderDirection = 'desc';

    /**
     * @var Closure
     */
    public $secondaryOrderCallback;

    /**
     * @var int
     */
    public $defaultPerPage = 15;

    /**
     * @var string
     */
    public $searchColumn = 'name';

    /**
     * @var array
     */
    public $filterColumns = [];

    /**
     * @var Closure
     */
    public $searchCallback;

    /**
     * @var array
     */
    private $params;

    /**
     * @var bool
     */
    public $dontSort;

    public function __construct($model, array $params)
    {
        $this->model = $model;
        $this->params = $this->toCamelCase($params);
        $this->query = $model->newQuery();
    }

    /**
     * @return LengthAwarePaginator
     */
    public function paginate()
    {
        $with = array_filter(explode(',', $this->param('with', '')));
        $withCount = array_filter(explode(',', $this->param('withCount', '')));
        $searchTerm = $this->param('query');
        $order = $this->getOrder();
        $perPage = $this->param('perPage', $this->defaultPerPage);
        $page = (int) $this->param('page', 1);

        // load specified relations and counts
        if ( ! empty($with)) $this->query->with($with);
        if ( ! empty($withCount)) $this->query->withCount($withCount);

        // search
        if ($searchTerm) {
            if ($this->searchCallback) {
                call_user_func($this->searchCallback, $this->query, $searchTerm);
            } else {
                $this->query->where($this->searchColumn, 'like', "$searchTerm%");
            }
        }

        $this->applyFilters();

        // order
        if ( ! $this->dontSort) {
            if ($this->isRawOrder($order['col'])) {
                $this->query->orderByRaw($order['col']);
            } else {
                $this->query->orderBy($order['col'], $order['dir']);
            }

            if ($this->secondaryOrderCallback) {
                call_user_func($this->secondaryOrderCallback, $this->query, $order['col'], $order['dir']);
            }
        }

        $countCacheKeyQuery = $this->query->toBase()->cloneWithout(['columns', 'orders', 'limit', 'offset']);
        $countCacheKye = base64_encode(Str::replaceArray('?', $countCacheKeyQuery->getBindings(), $countCacheKeyQuery->toSql()));
        $total = null;
        if ($countCacheKye) {
            $total = Cache::get($countCacheKye);
        }
        if (is_null($total)) {
             $total = $this->query->toBase()->getCountForPagination();
             if ($total > 500000) {
                Cache::put($countCacheKye, $total, Carbon::now()->addDay());
             }
        }

        $items = $total
            ? $this->query->forPage($page, $perPage)->get()
            : new Collection();

        return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
            'items' => $items, 'total' => $total, 'perPage' => $perPage, 'page' => $page,
        ]);
    }

    public function param(string $name, $default = null)
    {
        return Arr::get($this->params, Str::camel($name)) ?: $default;
    }

    public function setParam(string $name, $value): self
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * @return Builder
     */
    public function query() {
        return $this->query;
    }

    /**
     * Load specified relation counts with paginator items.
     *
     * @param mixed $relations
     * @return $this
     */
    public function withCount($relations)
    {
        $this->query->withCount($relations);
        return $this;
    }

    /**
     * Load specified relations of paginated items.
     *
     * @param mixed $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->query->with($relations);
        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->query->where($column, $operator, $value, $boolean);
        return $this;
    }

    public function setDefaultOrderColumns($column, $direction = 'desc'): self
    {
        $this->defaultOrderColumn = $column;
        $this->defaultOrderDirection = $direction;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrder() {
        // order provided as single string: "column|direction"
        if ($specifiedOrder = $this->param('order')) {
            $parts = preg_split("(\||:)", $specifiedOrder);
            $orderCol = Arr::get($parts, 0, $this->defaultOrderColumn);
            $orderDir = Arr::get($parts, 1, $this->defaultOrderDirection);
        } else {
            $orderCol = $this->param('orderBy', $this->defaultOrderColumn);
            $orderDir = $this->param('orderDir', $this->defaultOrderDirection);
        }

        return [
            'dir' => Str::snake($orderDir),
            'col' => !$this->isRawOrder($orderCol) ? Str::snake($orderCol) : $orderCol,
        ];
    }

    private function toCamelCase($params)
    {
        return collect($params)->keyBy(function($value, $key) {
            return Str::camel($key);
        })->toArray();
    }

    private function applyFilters()
    {
        foreach ($this->filterColumns as $column => $callback) {
            $column = is_int($column) ? $callback : $column;
            $column = Str::camel($column);
            if (isset($this->params[$column])) {
                $value = $this->params[$column];
                $column = Str::snake($column);

                // user specified callback
                if (is_callable($callback)) {
                    $callback($this->query, $value);

                // boolean filter
                } else if ($value === 'false' || $value === 'true') {
                    $this->applyBooleanFilter($column, $value);

                // filter by between date
                } else if (\Str::contains($column, '_at') && \Str::contains($value, ':')) {
                    $this->query()->whereBetween($column, explode(':', $value));

                // filter by specified column value
                } else {
                    $this->query()->where($column, $value);
                }
            }
        }
    }

    /**
     * @param string $column
     * @param string $value
     */
    private function applyBooleanFilter($column, $value)
    {
        // cast "true" or "false" to boolean
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $casts = $this->model->getCasts();

        // column is a simple boolean type
        if (Arr::get($casts, $column) === 'boolean') {
            $this->query()->where($column, $value);

            // column has actual value, test whether it's null or not by default
        } else {
            if ($value) {
                $this->query()->whereNotNull($column);
            } else {
                $this->query()->whereNull($column);
            }
        }
    }

    private function isRawOrder(string $order): bool
    {
        return Str::contains($order, ['(', ')']);
    }
}
