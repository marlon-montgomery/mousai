<?php namespace Common\Database\Datasource;

use Common\Database\Datasource\Filters\AlgoliaFilter;
use Common\Database\Datasource\Filters\MeilisearchFilter;
use Common\Database\Datasource\Filters\MysqlFilter;
use Common\Database\Datasource\Filters\TntFilter;
use Common\Search\Searchable;
use Common\Workspaces\ActiveWorkspace;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use const App\Providers\WORKSPACED_RESOURCES;

class DataSource
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var array
     */
    private $params;

    /**
     * @var DatasourceFilters
     */
    protected $filters;

    public function __construct($model, array $params)
    {
        $this->model = $model->getModel();
        $this->params = $this->toCamelCase($params);
        $this->builder = $model->newQuery();
    }

    public function paginate(): LengthAwarePaginator
    {
        $this->buildQuery();
        $perPage = $this->limit();
        $page = (int) $this->param('page', 1);

        $total = $this->builder->toBase()->getCountForPagination();
        $items = $total
            ? $this->builder->forPage($page, $perPage)->get()
            : new Collection();

        return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
            'items' => $items,
            'total' => $total,
            'perPage' => $perPage,
            'page' => $page,
        ]);
    }

    public function get(): Collection
    {
        $this->buildQuery();
        return $this->builder->limit($this->limit())->get();
    }

    public function param(string $name, $default = null)
    {
        return Arr::get($this->params, Str::camel($name)) ?: $default;
    }

    public function withCount(array $relations): self
    {
        $this->builder->withCount($relations);
        return $this;
    }

    public function with(array $relations): self
    {
        $this->builder->with($relations);
        return $this;
    }

    public function where(string $key, string $operator, $value): self
    {
        $this->filters->where($key, $operator, $value);
        return $this;
    }

    public function select(array $columns)
    {
        $this->builder->select($columns);
    }

    private function buildQuery()
    {
        $with = array_filter(explode(',', $this->param('with', '')));
        $withCount = array_filter(explode(',', $this->param('withCount', '')));
        $searchTerm = $this->param('query');

        // load specified relations and counts
        if (!empty($with)) {
            $this->builder->with($with);
        }
        if (!empty($withCount)) {
            $this->builder->withCount($withCount);
        }

        $this->filters = new DatasourceFilters($this->param('filters'), $this->model);
        $this->applyWorkspaceFilter();

        $filterer = $this->resolveFilterer($searchTerm);
        (new $filterer($this->builder, $this->filters, $searchTerm))->apply();

        $this->setOrder();
    }

    private function applyWorkspaceFilter(): void
    {
        if (
            !config('common.site.workspaces_integrated') ||
            !in_array(get_class($this->model), WORKSPACED_RESOURCES)
        ) {
            return;
        }

        if ($workspaceId = request()->header(ActiveWorkspace::HEADER)) {
            $this->where('workspace_id', '=', $workspaceId);
        } elseif ($userId = $this->param('userId')) {
            $this->where('user_id', '=', $userId)->where(
                'workspace_id',
                '=',
                null,
            );
        }
    }

    private function resolveFilterer(string $searchTerm = null): string
    {
        $driver = config('scout.driver');
        if (
            !$searchTerm ||
            !in_array(Searchable::class, class_uses_recursive($this->model)) ||
            $driver === 'mysql'
        ) {
            return MysqlFilter::class;
        } elseif ($driver === 'meilisearch') {
            return MeilisearchFilter::class;
        } elseif ($driver === 'tntsearch') {
            return TntFilter::class;
        } elseif ($driver === 'algolia') {
            return AlgoliaFilter::class;
        }
    }

    private function setOrder()
    {
        $defaultOrderDir = 'desc';
        $defaultOrderCol = 'updated_at';

        // order might be a single string: "column|direction"
        if ($specifiedOrder = $this->param('order')) {
            $parts = preg_split('(\||:)', $specifiedOrder);
            $orderCol = Arr::get($parts, 0, $defaultOrderCol);
            $orderDir = Arr::get($parts, 1, $defaultOrderDir);
            // order might be as separate params
        } elseif ($this->param('orderBy') || $this->param('orderDir')) {
            $orderCol = $this->param('orderBy');
            $orderDir = $this->param('orderDir');
            // if order is not provided via params, first default to orders that are already
            // set on the builder. If none are set, try ordering be relevance, if it's a search
            // query and using mysql fulltext, finally default to "updated_at" column
        } elseif (empty($this->builder->getQuery()->orders)) {
            $orderCol = $this->hasRelevanceColumn()
                ? 'relevance'
                : $defaultOrderCol;
            $orderDir = $defaultOrderDir;
        }

        if (isset($orderCol)) {
            $orderCol =
                $orderCol === 'relevance'
                    ? 'relevance'
                    : $this->model->qualifyColumn($orderCol);
            $this->builder->orderBy(Str::snake($orderCol), $orderDir ?? 'desc');
        }
    }

    private function toCamelCase(array $params): array
    {
        return collect($params)
            ->keyBy(function ($value, $key) {
                return Str::camel($key);
            })
            ->toArray();
    }

    private function hasRelevanceColumn(): bool
    {
        return !!Arr::first(
            $this->builder->getQuery()->columns ?? [],
            function ($col) {
                return $col instanceof Expression &&
                    Str::endsWith($col->getValue(), 'AS relevance');
            },
        );
    }

    private function limit(): int
    {
        return $this->param('perPage') ??
            ($this->builder->getQuery()->limit ?? 15);
    }
}
