<?php

namespace Common\Core;

use Common\Settings\Settings;
use GuzzleHttp\Client;

class HttpClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param array $params
     */
    public function __construct($params = [])
    {
        if ( ! isset($params['exceptions'])) $params['exceptions'] = false;
        if ( ! isset($params['timeout'])) $params['timeout'] = 2;
        $defaultVerify = (bool) app(Settings::class)->get('https.enable_cert_verification', true);
        if ( ! isset($params['verify'])) $params['verify'] = $defaultVerify;
        $this->client = new Client($params);
    }

    /**
     * @param string $url
     * @param array $params
     * @return array|string
     */
    public function get(string $url, array $params = [])
    {
        $r = $this->client->get($url, $params);

        if ($r->getStatusCode() === 429 && $r->hasHeader('Retry-After')) {
            $seconds = $r->getHeader('Retry-After') ? $r->getHeader('Retry-After') : 5;
            sleep((int) $seconds);
            $r = $this->get($url);
        }

        $contents = is_string($r) ? $r : $r->getBody()->getContents();
        $json = json_decode($contents, true);
        return $json ? $json : $contents;
    }

    /**
     * @param string $url
     * @param array $params
     * @return array
     */
    public function post($url, $params = [])
    {
        $r = $this->client->post($url, $params);

        if ($r->getStatusCode() === 429 && $r->hasHeader('Retry-After')) {
            $seconds = $r->getHeader('Retry-After') ? $r->getHeader('Retry-After') : 5;
            sleep($seconds);
            $r = $this->get($url);
        }

        $contents = $r->getBody()->getContents();
        $json = json_decode($contents, true);
        return $json ? $json : $contents;
    }
}

