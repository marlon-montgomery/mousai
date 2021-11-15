<?php

namespace Common\Settings\Validators\MailCredentials;

use Arr;
use Common\Settings\Mail\GmailClient;
use Common\Settings\Validators\SettingsValidator;

class GmailApiCredentialsValidator implements SettingsValidator
{
    const KEYS = ['mail.handler'];

    public function fails($settings): ?array
    {
        if (
            Arr::get($settings, 'mail.handler') === 'gmailApi' &&
            !GmailClient::tokenExists()
        ) {
            return ['gmail_group' => __('Gmail account needs to be connected.')];
        }

        return null;
    }
}
