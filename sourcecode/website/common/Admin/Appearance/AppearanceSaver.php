<?php namespace Common\Admin\Appearance;

use Common\Admin\Appearance\Events\AppearanceSettingSaved;
use Common\Settings\DotEnvEditor;
use Common\Settings\Settings;
use File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Str;

class AppearanceSaver
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * Path to custom css theme.
     */
    const THEME_PATH = 'appearance/theme.css';

    /**
     * Path to stored user selected values for css theme.
     */
    const THEME_VALUES_PATH = 'appearance/theme-values.json';

    const CUSTOM_CSS_PATH = 'custom-code/custom-styles.css';
    const CUSTOM_HTML_PATH = 'custom-code/custom-html.html';

    const BASIC_SETTING = 'BASIC';
    const ENV_SETTING = 'ENV';

    /**
     * Local filesystem instance.
     *
     * @var Settings
     */
    private $settings;

    /**
     * Flysystem Instance.
     *
     * @var FilesystemManager
     */
    private $storage;

    /**
     * @var DotEnvEditor
     */
    private $envEditor;

    /**
     * @param Filesystem $fs
     * @param Settings $settings
     * @param FilesystemManager $storage
     * @param DotEnvEditor $envEditor
     */
    public function __construct(
        Filesystem $fs,
        Settings $settings,
        FilesystemManager $storage,
        DotEnvEditor $envEditor
    )
    {
        $this->fs = $fs;
        $this->settings = $settings;
        $this->storage = $storage;
        $this->envEditor = $envEditor;
    }

    /**
     * @param array $params
     */
    public function save($params)
    {
        foreach ($params as $key => $value) {
            if ($key === 'colors') {
                $this->saveTheme($value);
            } else if (Str::startsWith($key, 'env.')) {
                $this->saveEnvSetting($key, $value);
            } else if (Str::startsWith($key, 'custom-code.')) {
                $this->saveCustomCode($key, $value);
            } else if (is_string($value) && \Str::contains($value, 'branding-images')) {
                $this->saveImageSetting($key, $value);
            } else {
                $this->saveBasicSetting($key, $value);
            }
        }
    }

    /**
     * Delete old image and store new one for specified setting.
     *
     * @param string $key
     * @param string $value
     */
    private function saveImageSetting($key, $value)
    {
        $old = $this->settings->get($key);

        //delete old file for this image, if it exists
        $this->storage->disk('public')->delete($old);

        //store new image file path in database
        $this->saveBasicSetting($key, $value);
    }

    /**
     * Save specified setting into .env file.
     *
     * @param string $key
     * @param string $value
     */
    private function saveEnvSetting($key, $value)
    {
        $key = str_replace('env.', '', $key);
        $previousValue = env(strtoupper($key));

        $this->envEditor->write([
            $key => $value
        ]);

        event(new AppearanceSettingSaved(self::ENV_SETTING, $key, $value, $previousValue));
    }

    /**
     * Save basic setting to database or .env file.
     *
     * @param string $key
     * @param mixed $value
     */
    private function saveBasicSetting($key, $value)
    {
        $value = is_array($value) ? json_encode($value) : $value;
        $previousValue = $this->settings->get($key);

        if ($previousValue !== $value) {
            $this->settings->save([$key => $value]);
            event(new AppearanceSettingSaved(self::BASIC_SETTING, $key, $value, $previousValue));
        }
    }

    /**
     * Save generated CSS theme and user defined theme values to disk.
     *
     * @param array $params
     */
    private function saveTheme($params)
    {
        $this->storage->disk('public')->put(self::THEME_VALUES_PATH, json_encode($params['themeValues']));
        $this->storage->disk('public')->put(self::THEME_PATH, $params['theme']);
    }

    public function saveCustomCode($key, $value)
    {
        $path = $key === 'custom-code.css' ?
            self::CUSTOM_CSS_PATH :
            self::CUSTOM_HTML_PATH;

        if ( ! File::exists(public_path('storage/custom-code'))) {
            File::makeDirectory(public_path('storage/custom-code'));
        }

        return File::put(public_path("storage/$path"), trim($value));
    }
}
