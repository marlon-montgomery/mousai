<?php

namespace Common\Admin\Analytics\Actions;

class GetNullAnalyticsData implements GetAnalyticsData
{
    public function execute($channel) {
        return [
            'weeklyPageViews' => [
                'current' => [],
                'previous' => [],
            ],
            'monthlyPageViews' => [
                'current' => [],
                'previous' => [],
            ],
            'browsers' => [],
            'countries' => []
        ];
    }
}