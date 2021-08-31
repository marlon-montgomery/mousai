<?php namespace Common\Admin\Appearance;

use Common\Core\Prerender\MetaTags;
use Common\Settings\Settings;
use Exception;
use File;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Arr;

class AppearanceValues
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var FilesystemManager
     */
    private $storage;

    /**
     * Path to stored user selected values for css theme.
     */
    const THEME_VALUES_PATH = 'appearance/theme-values.json';

    /**
     * ENV values to include.
     */
    const ENV_KEYS = ['app_url', 'app_name'];

    /**
     * @var Client
     */
    private $http;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Filesystem $fs
     * @param FilesystemManager $storage
     * @param Client $http
     * @param Settings $settings
     */
    public function __construct(
        Filesystem $fs,
        FilesystemManager $storage,
        Client $http,
        Settings $settings
    )
    {
        $this->fs = $fs;
        $this->storage = $storage;
        $this->http = $http;
        $this->settings = $settings;
    }

    /**
     * Get user defined and default values for appearance editor.
     *
     * @return array
     */
    public function get()
    {
        // get default settings for the application
        $settings = config('common.default-settings');

        // add env settings
        $env = [];
        foreach (self::ENV_KEYS as $key) {
            $env['env.'.$key] = config(str_replace('_', '.', $key));
        }
        $settings[] = ['name' => 'env', 'value' => $env];

        // add custom code
        $settings[] = ['name' => 'custom-code.css', 'value' => $this->getCustomCodeValue(AppearanceSaver::CUSTOM_CSS_PATH)];
        $settings[] = ['name' => 'custom-code.html', 'value' => $this->getCustomCodeValue(AppearanceSaver::CUSTOM_HTML_PATH)];

        // add seo fields
        $seoFields = ['name' => 'seo_fields', 'value' => $this->prepareSeoValues()];

        foreach ($settings as $key => $setting) {
            if (\Str::contains($setting['name'], 'seo.')) {
                $seoFields['value'][] = $setting;
                unset($settings[$key]);
            }
        }

        array_push($settings, $seoFields);

        return array_values($settings);
    }

    /**
     * Prepare seo values for appearance editor.
     *
     * @return array
     */
    private function prepareSeoValues()
    {
        $flat = [];
        $seoConfig = config('seo');

        if ( ! $seoConfig) return [];

        $seo = Arr::except($seoConfig, 'common');
        $seo = array_filter($seo, function($config) {
            return is_array($config);
        });

        // resource groups meta tags for artist, movie, track etc.
        foreach ($seo as $resourceName => $resource) {
            // resource has config for each verb (show, index etc.)
            foreach ($resource as $verbName => $verb) {
                // verb has a list of meta tags (og:title, description etc.)
                foreach ($verb as $metaTag) {
                    $property = Arr::get($metaTag, 'property');
                    if (array_search($property, MetaTags::EDITABLE_TAGS) === false) continue;

                    $name = str_replace('og:', '', "$resourceName / $verbName / $property");
                    $name = str_replace('-', ' ', $name);

                    $key = "seo.$resourceName.$verbName.$property";
                    $defaultValue = $metaTag['content'];

                    $flat[] = [
                        'name' => $name,
                        'key' => $key,
                        'value' => $this->settings->get($key, $defaultValue),
                        'defaultValue' => $defaultValue,
                    ];
                }
            }
        }

        return $flat;
    }

    private function getCustomCodeValue($path)
    {
        try {
            return File::get(public_path("storage/$path"));
        } catch (Exception $e) {
            return '';
        }
    }
}
