<?php

namespace App\Services\Providers;

use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait SaveOrUpdate
{
    /**
     * @param array|Collection $values
     * @param string $table
     * @param bool $debug
     * @return void
     */
    protected function saveOrUpdate($values, $table, $debug = false)
    {
        $values = $values instanceof Arrayable ? $values->toArray() : $values;

        // make sure values and bindings don't contain any nested arrays
        $values = array_map(function($value) {
            if ( ! is_scalar($value)) {
                return array_filter($value, function($sub) {
                    return $sub instanceof Carbon || is_scalar($sub) || is_null($sub);
                });
            } else {
                return $value;
            }
        }, $values);
        $bindings = Arr::flatten($values);

        if (empty($values)) return;

        $first = head($values);

        //count how many inserts we need to make
        $amount = count($values);

        //count in how many columns we're inserting
        $columns = array_fill(0, count($first), '?');

        $columns = '(' . implode(', ', $columns) . ') ';

        //make placeholders for the amount of inserts we're doing
        $placeholders = array_fill(0, $amount, $columns);
        $placeholders = implode(',', $placeholders);

        $updates = [];

        //construct update part of the query if we're trying to insert duplicates
        foreach ($first as $column => $value) {
            $updates[] = "`$column` = COALESCE(values(`$column`), `$column`)";
        }

        $prefixed = DB::getTablePrefix() ? DB::getTablePrefix().$table : $table;

        $columns = array_map(function($column) {
            return "`$column`";
        }, array_keys($first));

        $query = "INSERT INTO {$prefixed} " . '(' . implode(',' , $columns) . ')' . ' VALUES ' . $placeholders .
            'ON DUPLICATE KEY UPDATE ' . implode(', ', $updates);

//        try {
//            DB::statement($query, $bindings);
//        } catch (QueryException $e) {
//            app(ExceptionHandler::class)->report($e);
//        }

        DB::statement($query, $bindings);
    }
}
