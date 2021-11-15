<?php

namespace Common\Files\Actions\Deletion;

use Common\Files\Events\FileEntriesDeleted;
use DB;
use League\Flysystem\FileNotFoundException;
use Storage;
use Common\Files\FileEntry;
use Illuminate\Support\Collection;

class PermanentlyDeleteEntries extends SoftDeleteEntries
{
    /**
     * Permanently delete file entries, related records and files from disk.
     *
     * @param Collection $entries
     * @return void
     */
    protected function delete(Collection $entries)
    {
        $entries = $this->loadChildEntries($entries, true);
        $this->deleteFiles($entries);
        $this->deleteEntries($entries);
        event(new FileEntriesDeleted($entries->pluck('id')->toArray(), true));
    }

    /**
     * Delete file entries from database.
     *
     * @param Collection $entries
     * @return bool|null
     */
    private function deleteEntries(Collection $entries) {
        $entryIds = $entries->pluck('id');

        // detach users
        DB::table('file_entry_models')->whereIn('file_entry_id', $entryIds)->delete();

        // detach tags
        DB::table('taggables')->where('taggable_type', FileEntry::class)->whereIn('taggable_id', $entryIds)->delete();

        return $this->entry->whereIn('id', $entries->pluck('id'))->forceDelete();
    }

    /**
     * Delete files from disk.
     *
     * @param Collection $entries
     * @return Collection
     */
    private function deleteFiles(Collection $entries)
    {
        return $entries->filter(function (FileEntry $entry) {
            return $entry->type !== 'folder';
        })->each(function(FileEntry $entry) {
            try {
                if ($entry->public) {
                    $entry->getDisk()->delete($entry->getStoragePath());
                } else {
                    $entry->getDisk()->deleteDir($entry->file_name);
                }
            } catch (FileNotFoundException $e) {
                //
            }
        });
    }
}
