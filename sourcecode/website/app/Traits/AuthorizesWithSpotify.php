<?php namespace App\Traits;

use App;
use App\Services\HttpClient;

trait AuthorizesWithSpotify {

    protected $token = false;

    public function authorize($spotifyId = null, $spotifySecret = null)
    {
        $spotifyId = $spotifyId ?: env('SPOTIFY_ID');
        $spotifySecret = $spotifySecret ?: env('SPOTIFY_SECRET');

        $client = new HttpClient();
        $response = $client->post('https://accounts.spotify.com/api/token', [
            'exceptions' => true,
            'headers' => ['Authorization' => 'Basic '.base64_encode($spotifyId.':'.$spotifySecret)],
            'form_params' => ['grant_type' => 'client_credentials']
        ]);
            
        $this->token = $response['access_token'];

        return $response;
    }
}