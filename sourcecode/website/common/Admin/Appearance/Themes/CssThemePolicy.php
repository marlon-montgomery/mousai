<?php

namespace Common\Admin\Appearance\Themes;

use Common\Auth\BaseUser;
use Common\Core\Policies\BasePolicy;

class CssThemePolicy extends BasePolicy
{
    public function index(BaseUser $user, $userId = null)
    {
        return $user->hasPermission('cssTheme.view') || $user->id === (int) $userId;
    }

    public function show(BaseUser $user, CssTheme $cssTheme)
    {
        return $user->hasPermission('cssTheme.view') || $cssTheme->user_id === $user->id;
    }

    public function store(BaseUser $user)
    {
        return $user->hasPermission('cssTheme.create');
    }

    public function update(BaseUser $user, CssTheme $cssTheme)
    {
        return $user->hasPermission('cssTheme.update') || $cssTheme->user_id === $user->id;
    }

    public function destroy(BaseUser $user, CssTheme $theme)
    {
        if ($theme->default_dark && app(CssTheme::class)->where('default_dark', true)->count() < 2)  {
            return $this->deny("Default dark theme can't be deleted");
        }

        if ($theme->default_light && app(CssTheme::class)->where('default_light', true)->count() < 2) {
            return $this->deny("Default light theme can't be deleted");
        }

        if (app(CssTheme::class)->count() <= 1) {
            return $this->deny("All themes can't be deleted");
        }

        return $user->hasPermission('cssTheme.delete') || $theme->user_id === $user->id;
    }
}
