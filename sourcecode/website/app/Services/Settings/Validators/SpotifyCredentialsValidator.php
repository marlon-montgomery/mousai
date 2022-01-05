<?php

namespace App\Services\Settings\Validators;

use App\Traits\AuthorizesWithSpotify;
use Common\Settings\Validators\SettingsValidator;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SpotifyCredentialsValidator implements SettingsValidator
{
    use AuthorizesWithSpotify;

    const KEYS = ['spotify_id', 'spotify_secret'];

    public function fails($values)
    {
        try {
            $this->authorize(Arr::get($values, 'spotify_id'), Arr::get($values, 'spotify_secret'));
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
        if ($errResponse['error'] === 'invalid_client') {
            if (Str::contains($errResponse['error_description'], 'secret')) {
                return ['spotify_secret' => 'This Spotify Secret is not valid.'];
            } else {
                return ['spotify_id' => 'This Spotify ID is not invalid.'];
            }
        } else {
            return ['spotify_group' => 'Could not validate spotify credentials, please try again later.'];
        }
    }
}
