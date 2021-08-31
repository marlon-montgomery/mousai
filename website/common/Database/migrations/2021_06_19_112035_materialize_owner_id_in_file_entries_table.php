<?php

use Common\Files\FileEntry;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Migrations\Migration;

class MaterializeOwnerIdInFileEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $entries = FileEntry::with(['users' => function(MorphToMany $builder) {
            $builder->where('owner', true)->limit(1);
        }])->cursor();

        foreach ($entries as $entry) {
            if ($owner = $entry->users->first()) {
                $entry->update(['owner_id' => $owner->id]);
            }
        }
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
