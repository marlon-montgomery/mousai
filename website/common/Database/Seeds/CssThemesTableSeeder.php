<?php namespace Common\Database\Seeds;

use App\User;
use Common\Admin\Appearance\Themes\CssTheme;
use Common\Settings\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

class CssThemesTableSeeder extends Seeder
{
    /**
     * @var Setting
     */
    private $theme;

    /**
     * @var User
     */
    private $user;

    /**
     * @param CssTheme $theme
     * @param User $user
     */
    public function __construct(CssTheme $theme, User $user)
    {
        $this->theme = $theme;
        $this->user = $user;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dark = config('common.themes.dark');
        $light = config('common.themes.light');

        $admin = $this->user->whereHas('permissions', function(Builder $builder) {
            $builder->where('name', 'admin');
        })->first();

        $darkTheme = $this->theme->where('default_dark', true)->orWhere('name', 'Dark')->first();
        if ( ! $darkTheme || ! $darkTheme->getRawOriginal('colors')) {
            if ($darkTheme) {
                $darkTheme->delete();
            }
            $this->theme->create([
                'name' => 'Dark',
                'is_dark' => true,
                'default_dark' => true,
                'colors' => $dark,
                'user_id' => $admin ? $admin->id : 1,
            ]);
        }

        $lightTheme = $this->theme->where('default_light', true)->orWhere('name', 'Light')->first();
        if ( ! $lightTheme || ! $lightTheme->getRawOriginal('colors')) {
            if ($lightTheme) {
                $lightTheme->delete();
            }
            $this->theme->create([
                'name' => 'Light',
                'default_light' => true,
                'user_id' => $admin ? $admin->id : 1,
                'colors' => $light,
            ]);
        }
    }
}
