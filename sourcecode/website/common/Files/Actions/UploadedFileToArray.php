<?php

namespace Common\Files\Actions;

use Common\Files\FileEntry;
use Common\Files\Traits\GetsEntryTypeFromMime;
use Illuminate\Http\UploadedFile;
use Str;

class UploadedFileToArray
{
    use GetsEntryTypeFromMime;

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
     * @param UploadedFile $file
     * @return array
     */
    public function execute(UploadedFile $file)
    {
        // TODO: move mime/extension/type guessing into separate class
        $originalMime = $file->getMimeType();

        if ($originalMime === 'application/octet-stream') {
            $originalMime = $file->getClientMimeType();
        }

        if ($originalMime === 'text/plain' && $file->getClientOriginalExtension() === 'csv') {
            $type = 'spreadsheet';
        }

        if ($originalMime === 'image/svg') {
            $originalMime = 'image/svg+xml';
        }

        if (Str::startsWith($originalMime, 'message/rfc')) {
            $originalMime = 'text/plain';
        }

        // TODO: have a list of supported image types and check against those
        if ($originalMime === 'image/vnd.dwg') {
            $originalMime = 'file';
        }

        return [
            'name' => $file->getClientOriginalName(),
            'file_name' => Str::random(40),
            'mime' => $originalMime,
            'type' => isset($type) ? $type : $this->getTypeFromMime($originalMime),
            'file_size' => $file->getSize(),
            'extension' => $this->getExtension($file, $originalMime),
        ];
    }

    /**
     * Extract file extension from specified file data.
     *
     * @param UploadedFile $file
     * @param string $mime
     * @return string
     */
    private function getExtension(UploadedFile $file, $mime)
    {
        if ($extension = $file->getClientOriginalExtension()) {
            return $extension;
        }

        $pathinfo = pathinfo($file->getClientOriginalName());

        if (isset($pathinfo['extension'])) return $pathinfo['extension'];

        return explode('/', $mime)[1];
    }
}
