<?php namespace App\Services;

use App;
use Common\Core\HttpClient as CommonHttpClient;
use Common\Settings\Settings;

class HttpClient extends CommonHttpClient
{
    /**
     * @param array $params
     */
	public function __construct($params = [])
	{
		$params['timeout'] = 8.0;
		$params['verify'] = (bool) App::make(Settings::class)->get('https.enable_cert_verification', true);
		parent::__construct($params);
	}
}