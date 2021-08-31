<?php

namespace Common\Admin\Analytics;

use Common\Admin\Analytics\Actions\GetAnalyticsData;
use Common\Admin\Analytics\Actions\GetDemoAnalyticsData;
use Common\Admin\Analytics\Actions\GetGoogleAnalyticsData;
use Common\Admin\Analytics\Actions\GetNullAnalyticsData;
use Illuminate\Support\ServiceProvider;
use Spatie\Analytics\Exceptions\InvalidConfiguration;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GetAnalyticsData::class, function () {
            if (config('common.site.demo')) {
                return new GetDemoAnalyticsData();
            } else {
                return $this->getGoogleAnalyticsData();
            }
        });
    }

    /**
     * @return GetGoogleAnalyticsData|GetNullAnalyticsData
     */
    private function getGoogleAnalyticsData()
    {
        try {
            return $this->app->make(GetGoogleAnalyticsData::class);
        } catch (InvalidConfiguration $e) {
            return new GetNullAnalyticsData();
        }
    }
}
