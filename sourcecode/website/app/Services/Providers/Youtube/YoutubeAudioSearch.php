<?php namespace App\Services\Providers\Youtube;

use App;
use App\Artist;
use App\Services\HttpClient;
use App\Track;
use Carbon\Carbon;
use Common\Settings\Settings;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class YoutubeAudioSearch {

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    /**
     * @param int $trackId
     * @param string $artistName
     * @param string $trackName
     * @return array
     */
    public function search($trackId, $artistName, $trackName)
    {
        // track and artist name is double encoded on the frontend
        // as laravel does not support encoded forward slashes in url
        $artistName = urldecode($artistName);
        $trackName = urldecode($trackName);

        if ($this->settings->get('youtube.search_method') === 'site') {
            $results = $this->viaScraping($artistName, $trackName);
        } else {
            $results = $this->viaApi($artistName, $trackName);
        }

        if ($this->settings->get('youtube.store_id') && count($results)) {
            app(Track::class)->where('id', $trackId)->update(['youtube_id' => $results[0]['id']]);
        }

        return $results;
    }

    /**
     * Scrape youtube site search page to find a video for specified track.
     *
     * @param string $artistName
     * @param string $trackName
     * @return array
     */
    private function viaScraping($artistName, $trackName)
    {
        $query = $this->buildYoutubeSearchQuery($artistName, $trackName, true);
        $client = new HttpClient(['exceptions' => true, 'headers' => ['Accept-Language' => 'en']]);
        $youtubeUrl = "https://www.youtube.com/results?search_query=$query";

        $html = $client->get($youtubeUrl);

        // youtube search results page was not rendered yet, need to extract json
        if (Str::contains($html, 'ytInitialData')) {
            preg_match('/ytInitialData"?]? = (.+?);\s?(\n|<\/script>)/ms', $html, $matches);
            $json = $matches[1];
            $json = json_decode($json, true);
            $contents = Arr::first($json['contents']['twoColumnSearchResultsRenderer']['primaryContents'])['contents'];
            $results = Arr::first($contents, function($content) {
                return !array_key_exists('carouselAdRenderer', Arr::first(Arr::first(Arr::first($content))));
            });
            $results = Arr::first(Arr::first($results));

            $results = array_filter($results, function ($result) {
                return isset($result['videoRenderer']);
            });
            $results = array_slice($results, 0, 3);
            $results = array_map(function($result) use($json) {
                $result = $result['videoRenderer'];
                return [
                    'title' => $result['title']['runs'][0]['text'],
                    'id' => $result['videoId'],
                ];
            }, $results);
        // youtube search results page was rendered, can crawl html
        } else {
            $results = [];
            $crawler = new Crawler($html);
            $crawler->filter('#results [data-context-item-id]')->slice(0, 3)->each(function(Crawler $node) use(&$results) {
                $videoId = head($node->extract(['data-context-item-id']));
                $title = head($node->filter('a[title]')->extract(['_text']));
                $results[] = ['title' => $title, 'id' => $videoId];
            });
        }

        return $results;
    }

    /**
     * Use youtube data api to find a video for specified track.
     *
     * @param string $artistName
     * @param string $trackName
     * @return array
     */
    private function viaApi($artistName, $trackName)
    {
        $params = $this->getParams($artistName, $trackName);
        $client = new HttpClient([
            'headers' =>  ['Referer' => url('')],
            'base_uri' => 'https://www.googleapis.com/youtube/v3/',
            'exceptions' => true
        ]);

        try {
            $response = $client->get('search', ['query' => $params]);
        } catch (ConnectException $e) {
            // connection timeouts happen sometimes,
            // there's no need to do anything extra
            return [];
        }

        return array_map(function($item) {
            return ['title' => $item['snippet']['title'], 'id' => $item['id']['videoId']];
        }, Arr::get($response, 'items'));
    }

    private function getParams($artist, $track)
    {
        $params = [
            'q' => $this->buildYoutubeSearchQuery($artist, $track),
            'key' => $this->settings->getRandom('youtube_api_key'),
            'part' => 'snippet',
            'fields' => 'items(id(videoId), snippet(title))',
            'maxResults' => 3,
            'type' => 'video',
            'videoEmbeddable' => 'true',
            'videoCategoryId' => 10, //music
            'topicId' => '/m/04rlf' //music (all genres)
        ];

        if ($regionCode = $this->settings->get('youtube.region_code')) {
            $params['regionCode'] = strtoupper($regionCode);
        }

        return $params;
    }

    private function buildYoutubeSearchQuery($artist, $track, $encode = false)
    {
        $append = '';

        //if "live" track is not being requested, append "video" to search
        //query to prefer music videos over lyrics and live videos.
        if ( ! Str::contains(strtolower($track), '- live')) {
            //$append = 'video';
        }

        $artist = $encode ? urlencode($artist) : $artist;
        $track = $encode ? urlencode($track) : $track;
        return trim("$artist+$track+$append");
    }
}
