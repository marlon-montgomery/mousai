<?php

namespace App\Services\Settings\Validators;

use App\Services\HttpClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Common\Settings\Validators\SettingsValidator;
use Illuminate\Support\Str;

class LastfmCredentialsValidator implements SettingsValidator
{
    const KEYS = ['lastfm_api_key'];
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'http://ws.audioscrobbler.com/2.0/',
            'exceptions' => true,
        ]);
    }

    public function fails($values)
    {
        $apiKey = Arr::get($values, 'lastfm_api_key');

        try {
            $this->httpClient->get("?method=tag.getTopTags&api_key=$apiKey&format=json");
        } catch (ClientException $e) {
            $errResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            return $this->getMessage($errResponse);
        }
    }

    /**
     * @param array $errResponse
     * @return array
     */
    private function getMessage($errResponse)
    {
        if (Str::contains($errResponse['message'], 'Invalid API key')) {
            return ['lastfm_api_key' => 'This Last.FM API key is not valid.'];
        } else {
            return ['lastfm_api_key' => 'Could not validate this Last.FM API key, please try again later.'];
        }
    }
}
