<?php

namespace Common\Plays;

use Auth;
use Illuminate\Database\Eloquent\Builder;

trait FindsCurrentUserPlays
{
    public function scopeForCurrentUser(Builder $builder): Builder
    {
        if (Auth::check()) {
            return $builder->where('user_id', Auth::id());
        } else {
            return $builder->where('ip', getIp());
        }
    }
}
