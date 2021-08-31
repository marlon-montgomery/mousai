<?php

namespace Common\Settings\Validators;

use Exception;
use Sentry\Dsn;

class LoggingCredentialsValidator
{
    const KEYS = ['sentry_dsn'];

    public function fails($settings)
    {
        try {
            Dsn::createFromString($settings['sentry_dsn']);
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
        return ['logging_group' => 'This sentry DSN is not valid.'];
    }
}
