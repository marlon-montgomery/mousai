<?php

namespace Common\Localizations;

use Carbon\Carbon;
use Common\Core\BaseController;

class UserLocaleController extends BaseController
{
    const COOKIE_NAME = 'selected_locale';

    public function update()
    {
        $localeCode = request()->get('locale');
        if (!$localeCode) {
            return $this->error(__('Locale code is required'));
        }

        if ($user = request()->user()) {
            $user->fill(['language' => $localeCode])->save();
        } else {
            cookie()->queue(
                self::COOKIE_NAME,
                $localeCode,
                1260,
                null,
                null,
                null,
                false,
                false,
            );
        }

        $locale = app(LocalizationsRepository::class)->getByNameOrCode(
            $localeCode,
        );

        return $this->success([
            'locale' => $locale,
        ]);
    }
}
