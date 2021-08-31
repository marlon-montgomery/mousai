<?php

namespace Common\Database\Datasource\Filters\Traits;

use Common\Database\Datasource\DatasourceFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

trait SupportsMysqlFilters
{
    public function applyMysqlFilters(DatasourceFilters $filters, $query)
    {
        foreach ($filters->getAll() as $filter) {
            if ($filter['value'] === 'null') {
                $filter['value'] = null;
            } else if ($filter['value'] === 'false') {
                $filter['value'] = false;
            } if ($filter['value'] === 'true') {
                $filter['value'] = true;
            }

            if (
                $filter['operator'] === 'has' ||
                $filter['operator'] === 'doesntHave'
            ) {
                $relName = $filter['key'];
                $relation = $query->getModel()->$relName();
                if ($relation instanceof HasMany || $relation instanceof HasOne) {
                    $query = $this->filterByHasManyRelation($query, $relation, $filter);
                } elseif ($relation instanceof BelongsToMany) {
                    $query = $this->filterByManyToManyRelation($query, $relation, $filter);
                }
            } else {
                $query = $query->where(
                    $filter['key'],
                    $filter['operator'],
                    $filter['value'],
                );
            }
        }

        return $query;
    }

    /**
     * @param HasOne|HasMany $relation
     * @return Model|Builder
     */
    private function filterByHasManyRelation(
        $query,
        $relation,
        array $filter
    ) {
        // use left join to check if model has any of specified relations
        if ($filter['value'] === '*') {
            $query
                ->leftJoin(
                    $relation->getRelated()->getTable(),
                    $relation->getQualifiedForeignKeyName(),
                    '=',
                    $relation->getQualifiedParentKeyName(),
                )
                ->where(
                    $relation->getQualifiedForeignKeyName(),
                    $filter['operator'] === 'doesntHave' ? '=' : '!=',
                    null,
                );
            // use left join to check if model has relation with specified ID
        } else {
            $relatedTable = $relation->getRelated()->getTable();
            $query
                ->leftJoin(
                    $relatedTable,
                    $relation->getQualifiedForeignKeyName(),
                    '=',
                    $relation->getQualifiedParentKeyName(),
                )
                ->where(
                    "$relatedTable.id",
                    $filter['operator'] === 'has' ? '=' : '!=',
                    $filter['value'],
                );
            if ($filter['operator'] === 'doesntHave') {
                $this->query->orWhere("$relatedTable.id", null);
            }
        }

        return $query;
    }

    /**
     * @param Builder|Model $query
     */
    private function filterByManyToManyRelation(
        $query,
        BelongsToMany $relation,
        array $filter
    ) {
        if ($filter['operator'] === 'has') {
            $query
                ->leftJoin(
                    $relation->getTable(),
                    $relation->getQualifiedParentKeyName(),
                    '=',
                    $relation->getQualifiedForeignPivotKeyName(),
                )
                ->where(
                    $relation->getQualifiedRelatedPivotKeyName(),
                    '=',
                    $filter['value'],
                );
        } elseif ($filter['operator'] === 'doesntHave') {
            $table = $query->getModel()->getTable();
            $query->whereNotIn("$table.id", function (
                Builder $builder
            ) use ($filter, $query) {
                $relName = $filter['key'];
                $relation = $query->getModel()->$relName();
                $builder
                    ->select($relation->getQualifiedForeignPivotKeyName())
                    ->from($relation->getTable())
                    ->where(
                        $relation->getQualifiedRelatedPivotKeyName(),
                        $filter['value'],
                    );
            });
        }

        return $query;
    }
}
