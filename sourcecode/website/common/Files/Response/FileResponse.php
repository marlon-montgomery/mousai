<?php

namespace Common\Files\Response;

use Common\Files\FileEntry;

interface FileResponse
{
    /**
     * @param FileEntry $entry
     * @param array $options
     * @return mixed
     */
    public function make(FileEntry $entry, $options);
}
