<?php

namespace Common\Localizations\Listeners;

use App\User;
use Common\Settings\Events\SettingsSaved;

class UpdateAllUsersLanguageWhenDefaultLocaleChanges
{
    /**
     * @param SettingsSaved $event
     */
    public function handle(SettingsSaved $event)
    {
        $settings = $event->envSettings;
        // change language for all users to new default locale as well
        if (array_key_exists('app_locale', $settings) && config('app.locale') !== $settings['app_locale']) {
            app(User::class)->where('language', '!=', $settings['app_locale'])
                ->update(['language' => $settings['app_locale']]);
        }
    }
}
