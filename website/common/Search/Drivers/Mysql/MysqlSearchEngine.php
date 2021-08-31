<?php namespace Common\Search\Drivers\Mysql;

use Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class MysqlSearchEngine extends Engine
{
    /**
     * Update the given model in the index.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return void
     */
    public function update($models)
    {
        //
    }

    /**
     * Remove the given model from the index.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return void
     */
    public function delete($models)
    {
        //
    }

    public function search(Builder $builder)
    {
        return $this->performSearch($builder, ['perPage' => $builder->limit]);
    }

    public function paginate(Builder $builder, $perPage, $page): Collection
    {
        return $this->performSearch($builder, [
            'limit' => $perPage,
            'offset' => $perPage * $page - $perPage,
        ]);
    }

    protected function performSearch(
        Builder $builder,
        array $options = []
    ): Collection {
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                null,
                $builder->query,
                $options,
            );
        }

        $query = $builder->model->mysqlSearch($builder->query);

        if (!empty($builder->orders)) {
            foreach ($builder->orders as $order) {
                $query->orderBy(
                    Arr::get($order, 'column'),
                    Arr::get($order, 'direction'),
                );
            }
        }

        if (isset($options['limit'])) {
            $query = $query->take($options['limit']);
        }
        if (isset($options['offset'])) {
            $query = $query->skip($options['offset']);
        }

        return $query->get();
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param mixed $results
     * @return Collection
     */
    public function mapIds($results)
    {
        return $results->pluck('id')->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param Builder $builder
     * @param mixed $results
     * @param Model $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        return $results;
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param mixed $results
     * @return int
     */
    public function getTotalCount($results)
    {
        return count($results);
    }

    /**
     * @inheritDoc
     */
    public function flush($model)
    {
        //
    }

    public function lazyMap(Builder $builder, $results, $model)
    {
        return LazyCollection::make($results);
    }

    public function createIndex($name, array $options = [])
    {
        //
    }

    public function deleteIndex($name)
    {
        //
    }
}
