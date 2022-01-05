<?php

namespace Common\Admin\Appearance\Themes;

use Auth;
use Illuminate\Support\Arr;

class CrupdateCssTheme
{
    /**
     * @var CssTheme
     */
    private $cssTheme;

    /**
     * @param CssTheme $cssTheme
     */
    public function __construct(CssTheme $cssTheme)
    {
        $this->cssTheme = $cssTheme;
    }

    /**
     * @param array $data
     * @param CssTheme $cssTheme
     * @return CssTheme
     */
    public function execute($data, $cssTheme = null)
    {
        if ( ! $cssTheme) {
            $cssTheme = $this->cssTheme->newInstance([
                'user_id' => Auth::id(),
                'colors' => $data['is_dark'] ? config('common.themes.dark') : config('common.themes.light')
            ]);
        }

        $attributes = Arr::only($data, [
            'name',
            'is_dark',
            'default_dark',
            'default_light',
            'colors',
        ]);

        $cssTheme->fill($attributes)->save();

        return $cssTheme;
    }
}