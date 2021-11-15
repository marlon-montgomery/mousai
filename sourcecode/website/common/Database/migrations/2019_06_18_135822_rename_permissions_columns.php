<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePermissionsColumns extends Migration
{
    public function up()
    {
        $tables = ['users', 'roles', 'billing_plans'];

        foreach ($tables as $tableName) {
            // rename permissions column
            if (Schema::hasColumn($tableName, 'permissions')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('permissions', 'legacy_permissions');
                });
            }

            // drop permissions index, if exists
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes($tableName);

                if (array_key_exists('legacy_permissions', $indexesFound)) {
                    $table->dropIndex('legacy_permissions');
                }

                if (array_key_exists('permissions', $indexesFound)) {
                    $table->dropIndex('permissions');
                }
            });
        }
    }

    public function down()
    {
        //
    }
}
