<?php namespace Common\Core\Bootstrap;

use App\User;
use Arr;
use Common\Admin\Appearance\Themes\CssTheme;
use Common\Auth\Roles\Role;
use Common\Localizations\Localization;
use Common\Localizations\LocalizationsRepository;
use Common\Settings\Settings;
use Illuminate\Http\Request;

class BaseBootstrapData implements BootstrapData
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Localization
     */
    protected $localizationRepository;

    /**
     * @var Role
     */
    protected $role;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param Settings $settings
     * @param Request $request
     * @param Role $role
     * @param LocalizationsRepository $localizationsRepository
     */
    public function __construct(
        Settings $settings,
        Request $request,
        Role $role,
        LocalizationsRepository $localizationsRepository
    )
    {
        $this->role = $role;
        $this->request = $request;
        $this->settings = $settings;
        $this->localizationRepository = $localizationsRepository;
    }

    public function getEncoded(): string
    {
        if ($this->data['user']) {
            $this->data['user'] = $this->data['user']->toArray();
        }

        return base64_encode(json_encode($this->data));
    }

    public function get($key = null)
    {
        return $key ? Arr::get($this->data, $key) : $this->data;
    }

    public function getSelectedTheme($key = null)
    {
        $selected = $this->get('themes.selected') ?: 'light';
        $value = Arr::get($this->data['themes'][$selected], $key);
        return $key === 'name' ? strtolower($value) : $value;
    }

    public function init()
    {
        $this->data['settings'] = $this->settings->all();
        $this->data['csrf_token'] = csrf_token();
        $this->data['settings']['base_url'] = config('app.url');
        $this->data['settings']['version'] = config('common.site.version');
        $this->data['user'] = $this->getCurrentUser();
        $this->data['guests_role'] = $this->role->where('guests', 1)->with('permissions')->first();
        $this->data['i18n'] = $this->getLocalizationsData() ?: null;
        $this->data['themes'] = $this->getThemes();
        $this->data['language'] = $this->data['i18n'] ? $this->data['i18n']['model']['language'] : 'en';

        if (config('common.site.notifications_integrated') && $this->data['user']) {
            $this->data['user']->unread_notifications_count = $this->data['user']
                ->unreadNotifications()->count();
        }

        return $this;
    }

    public function getThemes(): array
    {
        $themes = app(CssTheme::class)
            ->where('default_dark', true)
            ->orWhere('default_light', true)
            ->get();

        $defaultDark = new CssTheme(['name' => 'dark', 'is_dark' => true, 'colors' => config('common.themes.dark')]);
        $defaultLight = new CssTheme(['name' => 'light', 'is_light' => true, 'colors' => config('common.themes.light')]);

        $cookieName = slugify(config('app.name')).'_theme';
        $defaultMode = $this->settings->get('themes.default_mode', 'light');

        if ($this->settings->get('themes.user_change')) {
            if ($themeFromUrl = $this->request->get('be-mode')) {
                $selectedTheme = $themeFromUrl === 'light' ? 'light' : 'dark';
            } else {
                $selectedTheme = Arr::get($_COOKIE, $cookieName);
            }
        } else {
            $selectedTheme = $defaultMode;
        }

        return [
            'dark' => $themes->where('default_dark', true)->first() ?: $defaultDark,
            'light' => $themes->where('default_light', true)->first() ?: $defaultLight,
            'selected' => $selectedTheme ?: $defaultMode,
        ];
    }

    /**
     * Load current user and his roles.
     */
    public function getCurrentUser(): ?User
    {
        $user = $this->request->user();
        if ($user) {
            // load user subscriptions, if billing is enabled
            if (app(Settings::class)->get('billing.enable') && ! $user->relationLoaded('subscriptions')) {
                $user->load('subscriptions.plan');
            }

            // load user roles, if not already loaded
            if ( ! $user->relationLoaded('roles')) {
                $user->load('roles');
            }

            if ( ! $user->relationLoaded('permissions')) {
                $user->loadPermissions();
            }
        }

        return $user;
    }

    /**
     * Get currently selected i18n language.
     *
     * @return Localization
     */
    protected function getLocalizationsData()
    {
        if ( ! $this->settings->get('i18n.enable')) return null;

        //get user selected or default language
        $userLang = $this->request->user() ? $this->request->user()->language : null;

        if ( ! $userLang) {
            $userLang = config('app.locale');
        }

        if ($userLang) {
            return $this->localizationRepository->getByNameOrCode($userLang);
        }
    }
}
