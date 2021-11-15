<?php

namespace App\Services\Lyrics;

use App\Services\HttpClient;

class OvhLyricsProvider
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
        $response = $this->httpClient->get("https://api.lyrics.ovh/v1/$artistName/$trackName");

        if (isset($response['lyrics'])) {
            return nl2br($response['lyrics']);
        }
        return null;
    }
}
