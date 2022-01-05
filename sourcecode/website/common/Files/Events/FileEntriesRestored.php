<?php

namespace Common\Files\Events;

class FileEntriesRestored
{
    /**
     * @var array
     */
    public $entryIds;

    /**
     * @param array $entryIds
     */
    public function __construct($entryIds)
    {
        $this->entryIds = $entryIds;
    }
}
