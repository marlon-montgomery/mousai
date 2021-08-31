<?php

namespace Common\Files\Actions\Deletion;

use Arr;
use Common\Files\FileEntry;
use Gate;

class DeleteEntries
{
    public function execute(array $params)
    {
        $entryIds = $params['entryIds'] ?? $this->idsFromPaths($params['paths']);

        if (count($entryIds)) {
            Gate::authorize('destroy', [FileEntry::class, $entryIds]);

            if (Arr::get($params, 'soft')) {
                app(SoftDeleteEntries::class)->execute($entryIds);
            } else {
                app(PermanentlyDeleteEntries::class)->execute($entryIds);
            }
        }
    }

    private function idsFromPaths(array $paths): array
    {
        $filenames = array_map(function($path) {
            return basename($path);
        }, $paths);

        return app(FileEntry::class)
            ->whereIn('file_name', $filenames)
            ->pluck('id')->toArray();
    }
}
