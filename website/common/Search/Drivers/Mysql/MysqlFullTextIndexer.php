<?php

namespace Common\Search\Drivers\Mysql;

use Illuminate\Support\Facades\DB;

class MysqlFullTextIndexer
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var array
     */
    private $searchableFields;

    /**
     * @var bool
     */
    private $indexAlreadyExists;

    public function createOrUpdateIndex(string $model)
    {
        /**
         * @var Searchable $model
         */
        $model = new $model;
        $this->tableName = config('database.connections.mysql.prefix') . $model->getTable();
        $this->indexName = $model->searchableAs();

        $this->searchableFields = $model->getSearchableKeys(true);

        $this->indexAlreadyExists = $this->indexExists();

        if ( ! $this->indexAlreadyExists || $this->indexNeedsUpdate()) {
            $this->dropIndex();
            $fields = implode(',', $this->searchableFields);
            DB::statement("CREATE FULLTEXT INDEX $this->indexName ON $this->tableName ($fields)");
        }
    }

    private function indexExists(): bool
    {
        return !empty(DB::select("SHOW INDEX FROM $this->tableName WHERE Key_name = ?", [$this->indexName]));
    }

    private function indexNeedsUpdate(): bool
    {
        $currentIndexFields = $this->searchableFields;
        $expectedIndexFields = $this->getIndexFields();

        return $currentIndexFields != $expectedIndexFields;
    }

    private function getIndexFields(): array
    {
        $index = DB::select("SHOW INDEX FROM $this->tableName WHERE Key_name = ?", [$this->indexName]);

        $indexFields = [];

        foreach ($index as $idx) {
            $indexFields[] = $idx->Column_name;
        }

        return $indexFields;
    }

    private function dropIndex()
    {
       if ($this->indexAlreadyExists) {
           DB::statement("ALTER TABLE $this->tableName DROP INDEX $this->indexName");
       }
    }
}
