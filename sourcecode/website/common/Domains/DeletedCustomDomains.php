<?php

namespace Common\Domains;

use Illuminate\Foundation\Events\Dispatchable;

class DeletedCustomDomains
{
    use Dispatchable;

    public $domainIds;

    /**
     * @param int[] $domainIds
     */
    public function __construct($domainIds)
    {
        $this->domainIds = $domainIds;
    }
}
