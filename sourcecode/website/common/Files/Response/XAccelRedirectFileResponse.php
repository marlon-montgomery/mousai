<?php

namespace Common\Files\Response;

use Common\Files\FileEntry;

class XAccelRedirectFileResponse implements FileResponse
{
    /**
     * @param FileEntry $entry
     * @param array $options
     * @return mixed
     */
    public function make(FileEntry $entry, $options)
    {
        $disposition = $options['disposition'];
        header('X-Media-Root: ' . storage_path('app/uploads'));
        header("X-Accel-Redirect: /uploads/{$entry->getStoragePath($options['useThumbnail'])}");
        header("Content-Type: {$entry->mime}");
        header("Content-Disposition: $disposition; filename=\"".$entry->getNameWithExtension().'"');
        exit;
    }
}
