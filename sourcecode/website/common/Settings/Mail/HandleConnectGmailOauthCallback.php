<?php

namespace Common\Settings\Mail;

use Common\Auth\Oauth;
use File;
use Socialite;
use Illuminate\Contracts\View\View as ViewContract;

class HandleConnectGmailOauthCallback
{
    public function execute(string $provider): ViewContract
    {
        $profile = Socialite::with('google')->user();

        File::ensureDirectoryExists(basename(GmailClient::tokenPath()));
        File::put(
            GmailClient::tokenPath(),
            json_encode([
                'access_token' => $profile->token,
                'refresh_token' => $profile->refreshToken,
                'created' => now()->timestamp,
                'expires_in' => $profile->expiresIn,
                'email' => $profile->email,
            ]),
        );

        return app(Oauth::class)->getPopupResponse(self::class, [
            'profile' => $profile,
        ]);
    }
}
