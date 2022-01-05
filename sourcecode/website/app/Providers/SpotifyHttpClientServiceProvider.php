<?php namespace App\Providers;

use App\Services\Providers\Spotify\SpotifyHttpClient;
use Illuminate\Support\ServiceProvider;

class SpotifyHttpClientServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SpotifyHttpClient::class, function () {
            return new SpotifyHttpClient(['base_url' => 'https://api.spotify.com/v1/']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [SpotifyHttpClient::class];
    }
}
