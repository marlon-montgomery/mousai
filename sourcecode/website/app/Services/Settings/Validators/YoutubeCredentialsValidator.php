<?php

namespace App\Services\Settings\Validators;

use App\Services\HttpClient;
use Common\Settings\Settings;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Common\Settings\Validators\SettingsValidator;

class YoutubeCredentialsValidator implements SettingsValidator
{
    const KEYS = ['youtube_api_key', 'youtube.region_code'];

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->httpClient = new HttpClient([
            'headers' =>  ['Referer' => url('')],
            'base_uri' => 'https://www.googleapis.com/youtube/v3/',
            'exceptions' => true
        ]);
    }

    public function fails($settings)
    {
        try {
            $this->httpClient->get('search', ['query' => $this->getApiParams($settings)]);
        } catch(ClientException $e) {
            return $this->getMessage($e);
        }
    }

    private function getApiParams($settings)
    {
        $apiKey = Arr::get($settings, 'youtube_api_key', $this->settings->getRandom('youtube_api_key', ''));
        $regionCode = Arr::get($settings, 'youtube.region_code', $this->settings->get('youtube.region_code', ''));

        $apiKey = head(explode("\n", $apiKey));

        $params = [
            'q' => 'coldplay',
            'key' => $apiKey,
            'part' => 'snippet',
            'maxResults' => 1,
            'type' => 'video',
            'videoEmbeddable' => 'true',
            'videoCategoryId' => 10, //music
            'topicId' => '/m/04rlf' //music (all genres)
        ];

        if ($regionCode && $regionCode !== 'none') {
            $params['regionCode'] = strtoupper($regionCode);
        }

        return $params;
    }

    /**
     * @param ClientException $e
     * @return array
     */
    private function getMessage(ClientException $e)
    {
        $errResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
        $reason = Arr::get($errResponse, 'error.errors.0.reason');
        $message = Arr::get($errResponse, 'error.errors.0.message');
        $defaultMsg = 'Could not validate youtube API credentials. Please double check them.';

        if ($reason === 'accessNotConfigured' || $reason === 'ipRefererBlocked') {
            return ['youtube_api_key' => $message ?: $defaultMsg];
        } else if ($reason === 'keyInvalid') {
            return ['youtube_api_key' => 'This youtube API key is not valid.'];
        } else if ($reason === 'invalidRegionCode') {
            return ['youtube.region_code' => 'This youtube region code is not valid.'];
        }

        return ['youtube_group' => $defaultMsg];
    }
}
