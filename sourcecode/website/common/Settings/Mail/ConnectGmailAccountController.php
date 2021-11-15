<?php

namespace Common\Settings\Mail;

use Common\Auth\Oauth;
use Common\Core\BaseController;
use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Session;
use Socialite;

class ConnectGmailAccountController extends BaseController
{
    public function connectGmail()
    {
        Session::flash(
            Oauth::OAUTH_CALLBACK_HANDLER_KEY,
            HandleConnectGmailOauthCallback::class,
        );

        $driver = Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/gmail.readonly',
                'https://www.googleapis.com/auth/gmail.send',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent select_account',
            ]);

        return $driver->redirect();
    }

    public static function getConnectedEmail(): ?string
    {
        if (!class_exists(GmailClient::class)) {
            return null;
        }

        try {
            $data = json_decode(File::get(GmailClient::tokenPath()), true);
            return $data['email'];
        } catch (FileNotFoundException $e) {
            return null;
        }
    }
}
