<?php

namespace Common\Database\Datasource;

use Common\Database\Datasource\Filters\Traits\SupportsMysqlFilters;
use Common\Workspaces\ActiveWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use const App\Providers\WORKSPACED_RESOURCES;

class MysqlDataSource
{
    use SupportsMysqlFilters;

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

    private $queryBuilt = false;

    /**
     * @var DatasourceFilters
     */
    private $filters;

    /**
     * @var array
     */
    public $order = null;

    public function __construct(
        $model,
        array $params,
        DatasourceFilters $filters = null
    ) {
        $this->model = $model->getModel();
        $this->params = $this->toCamelCase($params);
        $this->builder = $model->newQuery();
        $this->filters =
            $filters ??
            new DatasourceFilters(
                $this->params['filters'] ?? null,
                $this->model,
            );
    }

    public function paginate(): LengthAwarePaginator
    {
        $this->buildQuery();
        $perPage = $this->limit();
        $page = (int) $this->param('page', 1);

        return $this->builder->paginate($perPage, ['*'], null, $page);
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

    public function buildQuery()
    {
        if ($this->queryBuilt) {
            return;
        }
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

        $this->applyMysqlFilters($this->filters, $this->builder);
        $this->applyWorkspaceFilter();

        if ($searchTerm) {
            $this->builder->mysqlSearch($searchTerm);
        }

        // allow caller class to override order or
        // prevent it completely by setting "false"
        if ($this->order !== false) {
            $order = $this->getOrder();
            if (isset($order['col'])) {
                $order['col'] =
                    $order['col'] === 'relevance'
                        ? 'relevance'
                        : // can't qualify with table name because ordering by relationship count will not work
                        $order['col'];
                $this->builder->orderBy(
                    Str::snake($order['col']),
                    $order['dir'] ?? 'desc',
                );
            }
        }

        $this->queryBuilt = true;

        return $this;
    }

    private function applyWorkspaceFilter(): void
    {
        if (
            !config('common.site.workspaces_integrated') ||
            !config('common.site.new_workspace_filter') ||
            !in_array(get_class($this->model), WORKSPACED_RESOURCES)
        ) {
            return;
        }

        if ($workspaceId = request()->header(ActiveWorkspace::HEADER)) {
            $this->builder->where('workspace_id', $workspaceId);
        } elseif ($userId = $this->param('userId')) {
            $this->builder
                ->where('user_id', $userId)
                ->whereNull('workspace_id');
        }
    }

    public function getOrder(): array
    {
        $defaultOrderDir = 'desc';
        $defaultOrderCol = 'updated_at';

        if (isset($this->order['col'])) {
            $orderCol = $this->order['col'];
            $orderDir = $this->order['dir'];
            // order might be a single string: "column|direction"
        } elseif ($specifiedOrder = $this->param('order')) {
            $parts = preg_split('(\||:)', $specifiedOrder);
            $orderCol = Arr::get($parts, 0, $defaultOrderCol);
            $orderDir = Arr::get($parts, 1, $defaultOrderDir);
            // order might be as separate params
        } elseif ($this->param('orderBy') || $this->param('orderDir')) {
            $orderCol = $this->param('orderBy');
            $orderDir = $this->param('orderDir');
            // try ordering be relevance, if it's a search query and
            // using mysql fulltext, finally default to "updated_at" column
        } else {
            $orderCol = $this->hasRelevanceColumn()
                ? 'relevance'
                : $defaultOrderCol;
            $orderDir = $defaultOrderDir;
        }

        return [
            'col' => $orderCol,
            'dir' => $orderDir,
        ];
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
        return !!Arr::first($this->getQueryBuilder() ?? [], function ($col) {
            return $col instanceof Expression &&
                Str::endsWith($col->getValue(), 'AS relevance');
        });
    }

    private function limit(): int
    {
        return $this->param('perPage') ??
            ($this->getQueryBuilder()->limit ?? 15);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $query = $this->builder->getQuery();
        if ($query instanceof Builder) {
            $query = $query->getQuery();
        }
        return $query;
    }
}
