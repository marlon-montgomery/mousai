<?php

namespace Common\Files\Actions;

use Arr;
use Common\Files\Events\FileEntryCreated;
use Common\Files\FileEntry;
use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Image;
use Intervention\Image\Constraint;
use Storage;

class UploadFile
{
    /**
     * @param string $disk
     * @param UploadedFile|array $uploadedFile
     * @param array $params
     * @param FileEntry|null $fileEntry
     * @return FileEntry
     */
    public function execute($disk, $uploadedFile, $params, $fileEntry = null)
    {
        if (!$fileEntry) {
            $fileEntry = app(CreateFileEntry::class)->execute(
                $uploadedFile,
                $params,
            );
        }

        $this->storeUpload($disk, $fileEntry, $uploadedFile, $params);

        if ($disk !== 'public') {
            event(new FileEntryCreated($fileEntry, $params));
        }

        return $fileEntry;
    }

    /**
     * @param string $diskName
     * @param FileEntry $entry
     * @param UploadedFile|array|string $contents
     * @param array $params
     */
    private function storeUpload(
        $diskName,
        FileEntry $entry,
        $contents,
        $params
    ) {
        if ($diskName === 'public') {
            $disk = Storage::disk('public');
            $prefix = $entry->disk_prefix;
        } else {
            $disk = Storage::disk('uploads');
            $prefix = $entry->file_name;
        }

        $options = [
            'mimetype' => $entry->mime,
            'visibility' => $entry->public
                ? 'public'
                : config('common.site.remote_file_visibility'),
        ];

        if (is_a($contents, UploadedFile::class)) {
            $disk->putFileAs($prefix, $contents, $entry->file_name, $options);
        } else {
            // might have file meta as array or just file string contents
            $contents =
                is_array($contents) && Arr::get($contents, 'contents')
                    ? $contents['contents']
                    : $contents;
            $disk->put("$prefix/{$entry->file_name}", $contents, $options);
        }

        if ($diskName !== 'public') {
            try {
                $this->maybeCreateThumbnail(
                    $disk,
                    $entry,
                    $contents,
                    $options['visibility'],
                );
            } catch (Exception $e) {
                //
            }
        }
    }

    private function maybeCreateThumbnail(
        FilesystemAdapter $disk,
        FileEntry $entry,
        $contents,
        string $visibility
    ) {
        // only create thumbnail for images over 500KB in size
        if ($entry->type === 'image' && $entry->file_size > 500000) {
            $this->setMemoryLimit();
            $img = Image::make($contents)->orientate();

            $img->fit(350, 250, function (Constraint $constraint) {
                $constraint->upsize();
            });

            $img->encode($entry->extension === 'png' ? 'png' : 'jpg', 60);

            $options = [
                'mimetype' => $img->mime(),
                'visibility' => $visibility,
            ];

            $disk->put("{$entry->file_name}/thumbnail.jpg", $img, $options);

            $entry->fill(['thumbnail' => true])->save();
        }
    }

    private function setMemoryLimit()
    {
        $new = 512;
        $current = (int) ini_get('memory_limit');
        if ($current < $new) {
            @ini_set('memory_limit', "${$new}M");
        }
    }
}
