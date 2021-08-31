<?php

namespace App\Providers;

use App\Channel;
use App\Services\Admin\GetAnalyticsHeaderData;
use App\Services\AppBootstrapData;
use App\Services\UrlGenerator;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;
use Common\Core\Bootstrap\BootstrapData;
use Common\Core\Contracts\AppUrlGenerator;
use Illuminate\Support\ServiceProvider;
use Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(BootstrapData::class, AppBootstrapData::class);

        Route::bind("channel", function (
            $idOrSlug,
            \Illuminate\Routing\Route $route
        ) {
            if ($route->getActionMethod() === "destroy") {
                $channelIds = explode(",", $idOrSlug);
                return app(Channel::class)
                    ->whereIn("id", $channelIds)
                    ->get();
            } elseif (ctype_digit($idOrSlug)) {
                return app(Channel::class)->findOrFail($idOrSlug);
            } else {
                return app(Channel::class)
                    ->where("slug", $idOrSlug)
                    ->firstOrFail();
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            GetAnalyticsHeaderDataAction::class,
            GetAnalyticsHeaderData::class,
        );

        $this->app->bind(AppUrlGenerator::class, UrlGenerator::class);
    }
}
