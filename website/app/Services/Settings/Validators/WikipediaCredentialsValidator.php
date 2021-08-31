<?php

namespace App\Services\Settings\Validators;

use App\Services\HttpClient;
use Exception;
use Illuminate\Support\Arr;
use Common\Settings\Validators\SettingsValidator;

class WikipediaCredentialsValidator implements SettingsValidator
{
    const KEYS = ['wikipedia_language'];
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'exceptions' => true,
        ]);
    }

    public function fails($values)
    {
        $lang = Arr::get($values, 'wikipedia_language', 'en');

        try {
            $this->httpClient->get("https://$lang.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro=&explaintext=&titles=foo-bar&redirects=1&exlimit=4");
        } catch (Exception $e) {
            return $this->getMessage($e);
        }
    }

    /**
     * @param Exception $e
     * @return array
     */
    private function getMessage(Exception $e)
    {
        return ['wikipedia_language' => 'This wikipedia language is not valid.'];
    }
}