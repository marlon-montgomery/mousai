<?php

namespace Common\Settings\Validators\MailCredentials;

use Arr;
use Common\Settings\Mail\GmailClient;
use Common\Settings\Validators\SettingsValidator;
use Google\Service\Exception;

class GmailApiCredentialsValidator implements SettingsValidator
{
    const KEYS = ['mail.handler'];

    public function fails($settings): ?array
    {
        if (Arr::get($settings, 'mail.handler') === 'gmailApi') {
            if (!GmailClient::tokenExists()) {
                return [
                    'gmail_group' => __('Gmail account needs to be connected.'),
                ];
            }

            // init google pub sub
            try {
                app(GmailClient::class)->watch();
            } catch (Exception $e) {
                $decoded = json_decode($e->getMessage(), true);
                if (is_array($decoded) && isset($decoded['error']['message'])) {
                    return ['gmail_group' => $decoded['error']['message']];
                } else {
                    return [
                        'gmail_group' => $e->getMessage(),
                    ];
                }
            }
        }

        return null;
    }
}
