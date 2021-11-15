<?php namespace App\Services\Artists;

use App;
use App\Artist;
use App\Services\HttpClient;
use Common\Settings\Settings;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ArtistBio {

    /**
     * @var HttpClient
     */
	private $httpClient;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * ArtistBio constructor.
     *
     * @param HttpClient $httpClient
     * @param Settings $settings
     */
    public function __construct(HttpClient $httpClient, Settings $settings)
    {
        $this->httpClient = $httpClient;
        $this->settings = $settings;
    }

    /**
     * Get artist biography and images.
     *
     * @param Artist $artist
     * @return Artist
     */
	public function get(Artist $artist)
	{
        $provider = $this->settings->get('artist_bio_provider', 'wikipedia');

        if ($provider === 'wikipedia') {
            $bio = $this->getFromWikipedia($artist->name);
        }

        if (isset($bio)) {
            if ($bioContent = Arr::get($bio, 'content')) {
                $artist->profile()->updateOrCreate(['artist_id' => $artist->id], ['description' => $bioContent]);
            }
            if (count($bio['images'])) {
                $artist->profileImages()->delete();
                $artist->profileImages()->createMany($bio['images']);
            }
        }

        return $artist;
	}

    /**
     * Fetch artist bio and images from Wikipedia.
     *
     * @param string $name
     * @return array
     */
    private function getFromWikipedia($name)
    {
        // TODO: maybe add spotify bio and bio images provider, can scrape the site

        $lang = $this->settings->get('wikipedia_language', 'en');
        $url  = $this->makeWikipediaApiUrl($name, $lang);

        $response = $this->httpClient->get($url);
        if ( ! isset($response['query']['pages'])) return [];

        $response = $response['query']['pages'];

        //if we didn't find bio and language is not english, fetch bio in english
        if ( ! isset($response[max(array_keys($response))]['extract']) && $lang !== 'en') {
            $response = $this->httpClient->get($this->makeWikipediaApiUrl($name, 'en'));
            $response = $response['query']['pages'];
        }

        $bioResponse = $this->extractBioFromWikipediaResponse($response);

        $response = $this->httpClient->get($this->makeWikipediaApiUrl($name, 'en', 'images', $bioResponse['type']));

        if ( ! isset($response['query']['pages'])) {
            $filtered = []; $images = [];
        } else {
            $urls = array_map(function($item) {
                return ['url' => isset($item['imageinfo']) ? head($item['imageinfo'])['url'] : null];
            }, $response['query']['pages']);

            $images = array_filter($urls, function($image) {
                return ! Str::contains($image['url'], '.svg');
            });

            $filtered = array_filter($images, function($image) use($name) {
                return Str::contains($image['url'], $name);
            });
        }

        return ['content' => $bioResponse['bio'], 'images' => array_slice(count($filtered) > 3 ? $filtered : $images, 0, 4)];
    }

    /**
     * Extract artist biography from the correct wikipedia page.
     *
     * @param $response
     * @return array
     */
    private function extractBioFromWikipediaResponse($response)
    {
        if ( ! is_array($response) || empty($response)) return '';

        foreach($response as $item) {
            if (Str::contains($item['title'], 'singer') && isset($item['extract']) && $item['extract']) return ['bio' => $item['extract'], 'type' => 'singer'];
            if (Str::contains($item['title'], 'band') && isset($item['extract']) && $item['extract']) return ['bio' => $item['extract'], 'type' => 'band'];
            if (Str::contains($item['title'], 'rapper') && isset($item['extract']) && $item['extract']) return ['bio' => $item['extract'], 'type' => 'rapper'];
        }

        $length  = 0;
        $longest = '';

        foreach($response as $item) {
            if (isset($item['extract']) && $item['extract']) {
                if (strlen($item['extract']) > $length) {
                    $length = strlen($item['extract']); $longest = $item['extract'];
                }
            }
        }

        return ['bio' => $longest, 'type' => false];
    }

    /**
     * Make url for wikipedia artist api page.
     *
     * @param string  $name
     * @param string  $lang
     * @param boolean $getImages
     * @param string|boolean $type
     *
     * @return string
     */
    private function makeWikipediaApiUrl($name, $lang = 'en', $getImages = false, $type = false)
    {
        $name = str_replace(' ', '_', ucwords(strtolower($name)));

        if ($type) {
            $titles = $name."_($type)";
        } else {
            $titles = "$name|".$name."_(rapper)|".$name."_(band)|".$name."_(singer)";
        }

        if ($getImages) {
            return "https://en.wikipedia.org/w/api.php?action=query&titles=$titles&generator=images&gimlimit=30&prop=imageinfo&iiprop=url|dimensions|mime&format=json&redirects=1";
        }

        return "https://$lang.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro=&explaintext=&titles=$titles&redirects=1&exlimit=4";
    }
}
