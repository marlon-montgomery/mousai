<?php namespace App\Http\Controllers\Search;

use App;
use App\Services\Providers\ProviderResolver;
use App\Track;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Str;

class SearchController extends BaseController
{
    /**
     * @var ProviderResolver
     */
    private $provider;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    public function __construct(
        Request $request,
        Settings $settings,
        ProviderResolver $provider
    )
    {
        $this->request = $request;
        $this->settings = $settings;
        $this->provider = $provider;
    }

    public function index()
    {
        $modelTypes = explode(',', $this->request->get('types'));

        $limit = $this->request->get('limit', 3);
        $query = $this->request->get('query');
        $contentProvider = $this->provider->get('search', request('localOnly') ? 'local' : null);

        $response = [
            'query' => e($query),
            'results' => [],
        ];

        if ($query) {
            $modelTypes = array_filter($modelTypes, function($modelType) {
                // artist => App\Artist
                $model = 'App\\' . ucfirst($modelType);
                return Gate::inspect('index', $model)->allowed();
            });

            $results = $contentProvider->search($query, $limit, $modelTypes);
            $response['results'] = $results;

            if ($this->request->get('flatten')) {
                $response['results'] = Arr::flatten($response['results'], 1);
            }
        }

        return $this->success($response);
    }

    /**
     * @param int $trackId
     * @param string $artistName
     * @param string $trackName
     * @return array
     */
    public function searchAudio($trackId, $artistName, $trackName)
    {
        $this->authorize('index', Track::class);

        return $this->provider->get('audio_search')->search($trackId, $artistName, $trackName, 1);
    }

    /**
     * Remove artists that were blocked by admin from search results.
     *
     * @param array $results
     * @return array
     */
    private function filterOutBlockedArtists($results)
    {
        if (($artists = $this->settings->get('artists.blocked'))) {
            $artists = json_decode($artists);

            if (isset($results['artists'])) {
                foreach ($results['artists'] as $k => $artist) {
                    if ($this->shouldBeBlocked($artist['name'], $artists)) {
                        unset($results['artists'][$k]);
                    }
                }
            }

            if (isset($results['albums'])) {
                foreach ($results['albums'] as $k => $album) {
                    if (isset($album['artists'])) {
                        if ($this->shouldBeBlocked($album['artists'][0]['name'], $artists)) {
                            unset($results['albums'][$k]);
                        }
                    }
                }
            }

           if (isset($results['tracks'])) {
               foreach ($results['tracks'] as $k => $track) {
                   if (isset($track['album']['artists'])) {
                       if ($this->shouldBeBlocked($track['album']['artists'][0]['name'], $artists)) {
                           unset($results['tracks'][$k]);
                       }
                   }
               }
           }
        }

        return $results;
    }

    /**
     * Check if given artist should be blocked.
     *
     * @param string $name
     * @param array $toBlock
     * @return boolean
     */
    private function shouldBeBlocked($name, $toBlock)
    {
        foreach ($toBlock as $blockedName) {
            $pattern = '/' . str_replace('*', '.*?', strtolower($blockedName)) . '/i';
            if (preg_match($pattern, $name)) return true;
        }
    }
}
