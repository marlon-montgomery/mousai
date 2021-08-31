<?php

namespace App\Policies;

use App\Album;
use App\Track;
use App\User;
use Common\Core\Policies\BasePolicy;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Builder;

class TrackPolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'tracks.view') || $this->userOrGuestHasPermission($user, 'music.view');
    }

    public function show(?User $user, Track $track)
    {
        if ($track->owner_id === $user->id) {
            return true;
        } else if ($this->userOrGuestHasPermission($user, 'tracks.view') || $this->userOrGuestHasPermission($user, 'music.view')) {
            return true;
        } else if ($user) {
            $managedArtists = $user->artists()->pluck('artists.id');
            $trackArtists = $track->artists->pluck('pivot.artist_id');
            return $trackArtists->intersect($managedArtists)->isNotEmpty();
        }
        return false;
    }

    public function store(User $user)
    {
        // user can't create tracks at all
        if (!$user->hasPermission('tracks.create') && !$user->hasPermission('music.create')) {
            return false;
        }

        // user is admin, can ignore count restriction
        if ($user->hasPermission('admin')) {
            return true;
        }

        // user does not have any restriction on track minutes
        $maxMinutes = $user->getRestrictionValue('tracks.create', 'minutes');
        if (is_null($maxMinutes)) {
            return true;
        }

        $usedMS = $user->uploadedTracks()->sum('duration');
        $usedMinutes = floor($usedMS / 60000);

        // check if user did not go over their max quota
        if ($usedMinutes >= $maxMinutes) {
            $this->deny(__('policies.minutes_exceeded'), ['showUpgradeButton' => true]);
        }

        return true;
    }

    public function update(User $user, Track $track)
    {
        if ($track->owner_id === $user->id) {
            return true;
        } else if ($this->userOrGuestHasPermission($user, 'tracks.update') || $this->userOrGuestHasPermission($user, 'music.update')) {
            return true;
        } else if ($user) {
            $managedArtists = $user->artists()->pluck('artists.id');
            $trackArtists = $track->artists->pluck('pivot.artist_id');
            return $trackArtists->intersect($managedArtists)->isNotEmpty();
        }
        return false;
    }

    public function destroy(User $user, $trackIds)
    {
        if ($user->hasPermission('tracks.delete') || $user->hasPermission('music.delete')) {
            return true;
        } else {
            $managedArtists = $user->artists()->pluck('artists.id');
            $dbCount = Track::whereIn('tracks.id', $trackIds)
                ->where(function(Builder $builder) use($user, $managedArtists, $trackIds) {
                    $builder->where('owner_id', $user->id)
                        ->orWhereHas('artists', function(Builder $builder) use($managedArtists) {
                            $builder->whereIn('artists.id', $managedArtists);
                        }, '=', count($trackIds));
                })
                ->count();
            return $dbCount === count($trackIds);
        }
    }

    public function download(User $user, Track $track)
    {
        return app(Settings::class)->get('player.enable_download') && $user->hasPermission('music.download');
    }
}
