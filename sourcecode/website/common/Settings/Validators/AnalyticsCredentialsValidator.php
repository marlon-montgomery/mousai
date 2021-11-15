<?php

namespace Common\Settings\Validators;

use Config;
use Exception;
use Arr;
Use Str;
use Google_Service_Exception;
use Common\Admin\Analytics\Actions\GetGoogleAnalyticsData;

class AnalyticsCredentialsValidator
{
    const KEYS = ['analytics_view_id', 'analytics_service_email', 'analytics.tracking_code', 'certificate'];

    public function fails($settings)
    {
        $this->setConfigDynamically($settings);

        try {
            app(GetGoogleAnalyticsData::class)->execute(null);
        } catch (Exception $e) {
            return $this->getErrorMessage($e);
        }
    }

    private function setConfigDynamically($settings)
    {
        if ($viewId = Arr::get($settings, 'analytics_view_id')) {
            Config::set('analytics.view_id', $viewId);
        }
    }

    /**
     * @param Exception $e
     * @return array
     */
    private function getErrorMessage($e)
    {
        if ($e instanceof Google_Service_Exception) {
            $message = Arr::get($e->getErrors(), '0.message');
        } else if (Str::contains($e->getMessage(), "Could not find a credentials file at")) {
            return ['certificate' => 'Google Service Account Key File is required and has not been uploaded yet.'];
        } else {
            $message = $e->getMessage();
        }

        return ['analytics_group' => 'Invalid credentials: ' . $message];
    }
}
