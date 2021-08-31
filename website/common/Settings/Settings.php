<?php namespace Common\Settings;

use Cache;
use Carbon\Carbon;
use Exception;
use Arr;
use Illuminate\Support\Collection;

class Settings {

    /**
     * @var Collection
     */
    private $all;

    /**
     * Laravel config values that should be included with settings.
     * (display name for client => laravel config key)
     *
     * @var array
     */
    private $configKeys = [
        'billing.stripe_public_key' => 'services.stripe.key',
        'common.site.demo' => 'common.site.demo',
        'logging.sentry_public' => 'sentry.dsn',
        'i18n.default_localization' => 'app.locale',
        'billing.integrated' => 'common.site.billing_integrated',
        'workspaces.integrated' => 'common.site.workspaces_integrated',
        'notifications.integrated' => 'common.site.notifications_integrated',
        'notif.subs.integrated' => 'common.site.notif_subs_integrated',
        'api.integrated' => 'common.site.api_integrated',
        'branding.site_name' => 'app.name',
        'realtime.pusher_cluster' => 'broadcasting.connections.pusher.options.cluster',
        'realtime.pusher_key' => 'broadcasting.connections.pusher.key',
        'site.hide_docs_buttons' => 'common.site.hide_docs_buttons',
        'site.has_mobile_app' => 'common.site.has_mobile_app',
    ];

    /**
     * Create a new settings service instance.
     */
    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Get all application settings.
     *
     * @param bool $private
     * @return array
     */
    public function all($private = false)
    {
        $all = $this->all;

        // filter out private (server-only) settings
        if ( ! $private) {
            $all = $all->filter(function($setting) use($private) {
                return !$setting['private'];
            });
        }

        return $all->pluck('value', 'name')->toArray();
    }

    /**
     * Get a setting by key or return default.
     *
     * @param string $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $default;

        if ($setting = $this->find($key)) {
            $value = $setting['value'];
        }

        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Get a json setting by key and decode it.
     *
     * @param string $key
     * @param array|null $default
     * @return array
     */
    public function getJson($key, $default = null) {
        $value = $this->get($key, $default);
        if ( ! is_string($value)) return $value;
        return json_decode($value, true);
    }

    /**
     * Get random setting value from fields that
     * have multiple values separated by newline.
     *
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    public function getRandom($key, $default = null) {
        $key = $this->get($key, $default);
        $parts = explode("\n", $key);
        return $parts[array_rand($parts)];
    }

    /**
     * Check is setting with specified key exists.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return ! is_null($this->find($key));
    }

    /**
     * Set single setting. Does not persist in database.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $private
     *
     * @return void
     */
    public function set($key, $value, $private = false)
    {
        $this->all[$key] = ['name' => $key, 'value' => $value, 'private' => $private];
    }

    /**
     * Persist specified settings in database.
     *
     * @param array $data
     */
    public function save($data)
    {
        foreach ($data as $key => $value) {
            $setting = Setting::firstOrNew(['name' => $key]);
            $setting->value = ! is_null($value) ? $value : '';
            $setting->save();
            $this->set($key, $setting->value);
        }

        Cache::forget('settings.public');
    }

    /**
     * True if envato purchase code is required
     * for some functionality across the site.
     *
     * @return bool
     */
    public function envatoPurchaseCodeIsRequired()
    {
        return $this->get('envato.enable') && $this->get('envato.require_purchase_code');
    }

    private function find($key)
    {
        return Arr::get($this->all, $key);
    }

    /**
     * Load settings from database.
     */
    private function loadSettings()
    {
        $this->all = Cache::remember('settings.public', Carbon::now()->addDay(), function() {
            try {
                return Setting::select(['name', 'value', 'private'])->get()->mapWithKeys(function(Setting $setting) {
                    return [$setting->name => $setting->toArray()];
                });
            } catch (Exception $e) {
                return collect();
            }
        });

        // add config keys that should be included
        foreach ($this->configKeys as $clientKey => $configKey) {
            $this->set($clientKey, config($configKey));
        }
    }
}
