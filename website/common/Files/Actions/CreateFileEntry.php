<?php

namespace Common\Files\Actions;

use App\User;
use Auth;
use Common\Files\Events\FileEntryCreated;
use Common\Files\FileEntry;
use Common\Workspaces\ActiveWorkspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Arr;
use Str;

class CreateFileEntry
{
    /**
     * @var FileEntry
     */
    private $entry;

    /**
     * @param FileEntry $entry
     */
    public function __construct(FileEntry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * @param UploadedFile|array $fileOrData
     * @param $extra
     * @return FileEntry
     */
    public function execute($fileOrData, $extra)
    {
        if (is_array($fileOrData)) {
            $data = Arr::except($fileOrData, ['contents']);
        } else {
            $data = app(UploadedFileToArray::class)->execute($fileOrData);
        }
        
        // merge extra data specified by user
        $userId = Arr::get($extra, 'userId', Auth::id());
        $data = array_merge($data, [
            'parent_id' => Arr::get($extra, 'parentId'),
            'disk_prefix' => Arr::get($extra, 'diskPrefix'),
            'public' => !!Arr::get($extra, 'diskPrefix'),
            'owner_id' => $userId,
        ]);

        // public files will be stored with extension
        if ($data['public']) {
            $data['file_name'] = $data['file_name'] . '.' . $data['extension'];
        }

        $relativePath = Arr::get($extra, 'relativePath');
        $entries = new Collection();

        // uploading a folder
        if ($relativePath && !$data['public']) {
            $path = $this->createPath($relativePath, $data['parent_id'], $userId);
            $parent = $path['allParents']->last();
            if ($path['allParents']->isNotEmpty()) {
                $entries = $entries->merge($path['allParents']);
                $data['parent_id'] = $parent->id;
            }
        }

        $fileEntry = $this->entry->create($data);

        if ( ! Arr::get($data, 'public')) {
            $fileEntry->generatePath();
        }

        $entries = $entries->push($fileEntry);

        $entryIds = $entries->mapWithKeys(function($entry) {
            return [$entry->id => ['owner' => 1]];
        })->toArray();

        User::find($userId)->entries()->syncWithoutDetaching($entryIds);

        if (isset($path['newlyCreated'])) {
            $path['newlyCreated']->each(function(FileEntry $entry) {
                // make sure new folder gets attached to all
                // users who have access to the parent folder
                event(new FileEntryCreated($entry));
            });
        }

        if (isset($parent) && $parent) {
            $fileEntry->setRelation('parent', $parent);
        } else {
            $fileEntry->load('parent');
        }

        $entries->load('users');

        $fileEntry->setAttribute('all_parents', $path['allParents'] ?? []);
        // prevent eloquent trying to save "all_parents" into database
        $fileEntry->syncOriginalAttribute('all_parents');

        return $fileEntry;
    }

    /**
     * @param string $path
     * @param integer|null $parentId
     * @param integer $userId
     * @return array
     */
    private function createPath($path, $parentId, $userId)
    {
        $newlyCreated = collect();
        // remove file name from path and split into folder names
        $path = collect(explode('/', dirname($path)))->filter();
        if ($path->isEmpty()) return $path;

        $allParents = $path->reduce(function($parents, $name) use($parentId, $userId, $newlyCreated) {
            if ( ! $parents) $parents = collect();
            $parent = $parents->last();

            $values = [
                'type' => 'folder',
                'name' => $name,
                'file_name' => $name,
                'parent_id' => $parent ? $parent->id : $parentId,
                'workspace_id' => app(ActiveWorkspace::class)->id,
            ];

            // check if user already has a folder with that name and parent
            $folder = $this->entry->where($values)
                ->whereUser($userId)
                ->first();

            if ( ! $folder) {
                $folder = $this->entry->create($values);
                $folder->generatePath();
                $newlyCreated->push($folder);
            }

            return $parents->push($folder);
        });

        return ['allParents' => $allParents, 'newlyCreated' => $newlyCreated];
    }
}
