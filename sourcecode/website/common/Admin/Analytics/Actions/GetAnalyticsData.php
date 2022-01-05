<?php

namespace Common\Admin\Analytics\Actions;

use Illuminate\Support\Collection;

interface GetAnalyticsData
{
    /**
     * Get data for admin area analytics page from active provider.
     * (Demo or Google Analytics currently)
     *
     * @param string $channel
     * @return Collection
     */
    public function execute($channel);
}