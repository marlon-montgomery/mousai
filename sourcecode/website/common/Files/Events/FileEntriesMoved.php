<?php

namespace Common\Files\Events;

class FileEntriesMoved
{
    /**
     * @var array
     */
    public $entryIds;

    /**
     * @var null|integer
     */
    public $destination;

    /**
     * @var int|null
     */
    public $source;

    /**
     * @param array $entryIds
     * @param null|int $destination
     * @param null|int $source
     */
    public function __construct($entryIds, $destination, $source)
    {
        $this->entryIds = $entryIds;
        $this->destination = $destination;
        $this->source = $source;
    }
}
