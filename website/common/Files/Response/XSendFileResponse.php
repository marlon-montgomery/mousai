<?php

namespace Common\Files\Response;

use Common\Files\FileEntry;

class XSendFileResponse implements FileResponse
{
    /**
     * @param FileEntry $entry
     * @param array $options
     * @return mixed
     */
    public function make(FileEntry $entry, $options)
    {
        $path = storage_path('app/uploads').'/'.$entry->getStoragePath($options['useThumbnail']);
        $disposition = $options['disposition'];
        header("X-Sendfile: $path");
        header("Content-Type: {$entry->mime}");
        header("Content-Disposition: $disposition; filename=\"".$entry->getNameWithExtension().'"');
        exit;
    }
}
