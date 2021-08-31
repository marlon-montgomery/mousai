<?php

namespace Common\Core\Bootstrap;

use App\User;
use Common\Localizations\LocalizationsRepository;
use Spatie\Color\Hex;
use Spatie\Color\Rgb;
use Spatie\Color\Rgba;
use Str;

class MobileBootstrapData extends BaseBootstrapData
{
    public function init()
    {
        $themes = $this->getThemes();
        $themes['light']['colors'] = $this->mapColorsToRgba($themes['light']['colors']);
        $themes['dark']['colors'] = $this->mapColorsToRgba($themes['dark']['colors']);

        $this->data = [
            'themes' => $themes,
            'user' => $this->getCurrentUser(),
            'menus' => $this->getMobileMenus(),
            'locales' => app(LocalizationsRepository::class)->all()->map(function($value) {
                return $value['model'];
            }),
        ];
        return $this;
    }

    public function refreshToken(string $deviceName): self
    {
        /* @var User $user */
        $user = $this->data['user'];
        if ($user) {
            $user['access_token'] = $user->refreshApiToken($deviceName);
            $user->loadFcmToken();
        }
        return $this;
    }

    public function getCurrentUser(): ?User
    {
        /* @var User $user */
        if ($user = $this->request->user()) {
            $user->loadFcmToken();
            return $user;
        }
        return null;
    }

    private function getMobileMenus(): array
    {
        return array_values(array_filter($this->settings->getJson('menus'), function($menu) {
            return Str::startsWith($menu['position'], 'mobile-app');
        }));
    }

    private function mapColorsToRgba(array $colors): array
    {
        return array_map(function($color) {
            if (Str::startsWith($color, '#')) {
                $fullHex = str_pad($color, 7, substr($color, -1));
                $hex = Hex::fromString($fullHex)->toRgba();
                return [$hex->red(), $hex->green(), $hex->blue(), 1.0];
            } else if (Str::startsWith($color, 'rgba')) {
                $rgba = Rgba::fromString($color);
                return [$rgba->red(), $rgba->green(), $rgba->blue(), $rgba->alpha()];
            } else if ($color === 'black') {
                return [0, 0, 0, 1.0];
            } else {
                $rgb = Rgb::fromString($color);
                return [$rgb->red(), $rgb->green(), $rgb->blue(), 1.0];
            }
        }, $colors);
    }

}
