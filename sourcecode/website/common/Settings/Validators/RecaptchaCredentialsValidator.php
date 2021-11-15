<?php

namespace Common\Settings\Validators;

use Exception;
use Illuminate\Support\Arr;
use Common\Core\HttpClient;

class RecaptchaCredentialsValidator
{
    const KEYS = ['recaptcha.site_key', 'recaptcha.secret_key'];

    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'exceptions' => true
        ]);
    }

    public function fails($settings)
    {
        try {
            $response = $this->httpClient->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'response' => 'foo-bar',
                    'secret' => Arr::get($settings, 'recaptcha.secret_key'),
                ]
            ]);
            if ($response['success'] === false && Arr::get($response, 'error-codes.1') === 'invalid-input-secret') {
                $errors = ['recaptcha.secret_key' => 'This recaptcha secret key is not valid.'];
                if ( ! Arr::get($settings, 'recaptcha.site_key')) {
                    $errors['recaptcha.site_key'] = 'Recaptcha site key is required';
                }
                return $errors;
            }
        } catch (Exception $e) {
            return $this->getErrorMessage($e);
        }
    }

    /**
     * @param Exception $e
     * @return array
     */
    private function getErrorMessage($e)
    {
        return ['recaptcha-group' => $e->getMessage()];
    }
}
