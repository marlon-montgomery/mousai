<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class MoveUserFileEntryTableRecordsToFileEntryModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable('user_file_entry')) {
            return;
        }

        DB::table('user_file_entry')
            ->chunkById(100, function(Collection $records) {
                $records = $records->map(function($record) {
                    $record = (array) $record;
                    $record['model_type'] = User::class;
                    $record['model_id'] = $record['user_id'];
                    unset($record['user_id']);
                    return $record;
                });
                try {
                    DB::table('file_entry_models')->insert($records->toArray());
                } catch (Exception $e) {
                    //
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
