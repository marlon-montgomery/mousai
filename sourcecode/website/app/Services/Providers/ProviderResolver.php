<?php namespace App\Services\Providers;

use App;
use Common\Settings\Settings;
use Illuminate\Support\Str;

class ProviderResolver
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var array
     */
    private $defaults = [
        'artist' => 'local',
        'album' => 'local',
        'search' => 'local',
        'audio_search' => 'youtube',
        'genreArtists' => 'local',
        'radio' => 'spotify'
    ];

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function get(string $contentType, string $preferredProvider = null)
    {
        if ( ! $preferredProvider) {
            $preferredProvider = $this->resolvePreferredProviderFromSettings($contentType);
        }

        // make fully qualified provider class name
        $namespace = $this->getNamespace($contentType, $preferredProvider);

        if ( ! $contentType || ! class_exists($namespace)) {
            $namespace = $this->getNamespace($contentType, $this->defaults[$contentType]);
        }
        return App::make($namespace);
    }

    public function resolvePreferredProviderFromSettings(string $type): string
    {
        return $this->settings->get(Str::snake($type . '_provider'), $this->defaults[$type]);
    }

    private function getNamespace(string $type, string $provider): ?string
    {
        if ( ! $type || ! $provider) return null;

        // audio_search => audioSearch
        $type = Str::camel($type);

        // track:top => TopTracks
        $words = array_map(function($word) {
            return ucfirst($word);
        }, array_reverse(explode(':', $type)));
        $words = array_filter($words, function($word) {
            return !Str::startsWith($word, '$');
        });
        $type = join('', $words);
        if (count($words) > 1) {
            $type = Str::plural($type);
        }

        $provider = ucfirst(Str::camel($provider));
        return 'App\Services\Providers\\' . $provider . '\\' . $provider . $type;
    }
}
