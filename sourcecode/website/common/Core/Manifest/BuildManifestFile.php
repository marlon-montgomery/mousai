<?php

namespace Common\Core\Manifest;

use Common\Core\AppUrl;
use Common\Settings\Settings;

class BuildManifestFile
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function execute()
    {
        $replacements = [
            'DUMMY_NAME' => config('app.name'),
            'DUMMY_SHORT_NAME' => config('app.name'),
            'DUMMY_THEME_COLOR' => config('common.themes.light.--be-accent-default'),
            'DUMMY_BACKGROUND_COLOR' => config('common.themes.light.--be-background'),
            'DUMMY_START_URL' => app(AppUrl::class)->htmlBaseUri,
        ];

        @file_put_contents(
            public_path('client/manifest.json'),
            str_replace(
                array_keys($replacements),
                $replacements,
                file_get_contents(__DIR__.'/manifest-example.json')
            )
        );
    }
}
