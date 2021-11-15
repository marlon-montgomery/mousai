<?php

namespace Common\Auth\Traits;

use Common\Auth\BaseUser;
use Storage;
use Str;

trait HasAvatarAttribute
{
    public function getAvatarAttribute(?string $value)
    {
        // absolute link
        if ($value && Str::contains($value, '//')) {
            // change google/twitter avatar imported via social login size
            $value = str_replace(
                '.jpg?sz=50',
                ".jpg?sz=$this->gravatarSize",
                $value,
            );
            if ($this->gravatarSize > 50) {
                // twitter
                $value = str_replace('_normal.jpg', '.jpg', $value);
            }
            return $value;
        }

        // relative link (for new and legacy urls)
        if ($value) {
            return Storage::disk('public')->url(
                str_replace('storage/', '', $value),
            );
        }

        // gravatar
        $hash = md5(trim(strtolower($this->email)));

        return "https://www.gravatar.com/avatar/$hash?s={$this->gravatarSize}&d=retro";
    }

    /**
     * @param number $size
     * @return BaseUser
     */
    public function setGravatarSize($size)
    {
        $this->gravatarSize = $size;
        return $this;
    }
}
