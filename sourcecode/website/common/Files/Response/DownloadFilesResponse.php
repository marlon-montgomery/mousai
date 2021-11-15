<?php

namespace Common\Files\Response;

use Carbon\Carbon;
use Common\Files\FileEntry;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

class DownloadFilesResponse
{
    /**
     * @var FileResponseFactory
     */
    private $fileResponseFactory;

    /**
     * @param FileResponseFactory $fileResponseFactory
     */
    public function __construct(FileResponseFactory $fileResponseFactory)
    {
        $this->fileResponseFactory = $fileResponseFactory;
    }

    /**
     * @param Collection|FileEntry[] $entries
     * @return mixed
     */
    public function create($entries)
    {
        if ($entries->count() === 1 && $entries->first()->type !== 'folder') {
            return $this->fileResponseFactory->create($entries->first(), 'attachment');
        } else {
            $this->streamZip($entries);
        }
    }

    /**
     * @param Collection $entries
     * @return void
     */
    private function streamZip(Collection $entries)
    {
        $options = new Archive();
        $options->setSendHttpHeaders(true);

        // downloading multiple files from s3 will error out without this
        $options->setZeroHeader(true);

        $timestamp = Carbon::now()->getTimestamp();
        $zip = new ZipStream("download-$timestamp.zip", $options);

        $this->fillZip($zip, $entries);
        $zip->finish();
    }

    /**
     * @param ZipStream $zip
     * @param Collection $entries
     */
    private function fillZip(ZipStream $zip, Collection $entries) {
        $entries->each(function(FileEntry $entry) use($zip) {
            if ($entry->type === 'folder') {
                // this will load all children, nested at any level, so no need to do a recursive loop
                $children = $entry->allChildren()->get();
                $children->each(function(FileEntry $childEntry) use($zip, $entry, $children) {
                    $path = $this->transformPath($childEntry, $entry, $children);
                    if ($childEntry->type === 'folder') {
                        // add empty folder in case it has no children
                        $zip->addFile("$path/", '');
                    } else {
                        $this->addFileToZip($childEntry, $zip, $path);
                    }
                });
            } else {
                $this->addFileToZip($entry, $zip);
            }
        });
    }

    /**
     * @param FileEntry $entry
     * @param ZipStream $zip
     * @param string $path
     */
    private function addFileToZip(FileEntry $entry, ZipStream $zip, $path = null)
    {
        if ( ! $path) {
            $path = $entry->getNameWithExtension();
        }
        try {
            $stream = $entry->getDisk()->readStream($entry->getStoragePath());
            $zip->addFileFromStream($path, $stream);
        } catch (FileNotFoundException $e) {
            //
        }
    }

    /**
     * Replace entry IDs with names inside "path" property.
     *
     * @param FileEntry $entry
     * @param FileEntry $parent
     * @param Collection $folders
     * @return string
     */
    private function transformPath(FileEntry $entry, FileEntry $parent, Collection $folders)
    {
        if ( ! $entry->path) return $entry->getNameWithExtension();

        // '56/55/54 => [56,55,54]
        $path = array_filter(explode('/', $entry->path));
        $path = array_map(function($id) {
            return (int) $id;
        }, $path);

        //only generate path until specified parent and not root
        $path = array_slice($path, array_search($parent->id, $path));

        // last value will be id of the file itself, remove it
        array_pop($path);

        //map parent folder IDs to names
        $path = array_map(function($id) use($folders) {
            return $folders->find($id)->name;
        }, $path);

        return implode('/', $path) . '/' . $entry->getNameWithExtension();
    }

}
