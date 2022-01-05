<?php


namespace App\Services\Lyrics;


use App\Services\HttpClient;

class LyricsWikiaProvider
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getLyrics(string $artistName, string $trackName): ?string
    {
        $response = $this->httpClient->get("https://lyrics.fandom.com/api.php?action=lyrics&artist=$artistName&song=$trackName&fmt=realjson");

        if ( ! isset($response['url']) || ! $response['url'] || $response['lyrics'] === 'Not found') {
            return null;
        }

        $html = $this->httpClient->get($response['url']);

        preg_match("/<div class='lyricbox'>(.+?)<div class='lyricsbreak'>/", $html, $matches);

        if ( ! isset($matches[1])) {
            return null;
        }

        $noTags = strip_tags($matches[1], '<br>');

        $special = preg_replace_callback(
            "/(&#[0-9]+;)/",
            function($m) {
                return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
            },
            $noTags
        );

        return html_entity_decode($special);
    }
}
