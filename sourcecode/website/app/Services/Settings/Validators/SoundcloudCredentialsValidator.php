<?php

namespace App\Services\Settings\Validators;

use App\Services\HttpClient;
use Common\Settings\Settings;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Common\Settings\Validators\SettingsValidator;

class SoundcloudCredentialsValidator implements SettingsValidator
{
    const KEYS = ['soundcloud_api_key'];

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
            'base_uri' => 'https://api.soundcloud.com/',
            'exceptions' => true,
        ]);
    }

    public function fails($settings)
    {
        $apiKey = Arr::get(
            $settings,
            'soundcloud_api_key',
            config('common.site.soundcloud.key', '')
        );

        try {
            $this->httpClient->get("tracks?order=hotness&client_id={$apiKey}&q=coldplay&limit=1");
        } catch(ClientException $e) {
            return $this->getMessage($e);
        }
    }

    /**
     * @param ClientException $e
     * @return array
     */
    private function getMessage(ClientException $e)
    {
        return ['soundcloud_api_key' => 'This soundcloud API Key is not valid.'];
    }
}
