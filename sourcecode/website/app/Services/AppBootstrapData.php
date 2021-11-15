<?php namespace App\Services;

use App\Artist;
use Common\Core\Bootstrap\BaseBootstrapData;
use DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class AppBootstrapData extends BaseBootstrapData
{
    public function init()
    {
        parent::init();

        if (isset($this->data['user'])) {
            $this->getUserLikes();
            $this->getUserPlaylists();
            $this->loadUserFollowedUsers();
            $this->loadManagedArtists();
        }

        $this->data['settings']['spotify_is_setup'] = config('common.site.spotify.id') && config('common.site.spotify.secret');
        $this->data['settings']['lastfm_is_setup'] = !!config('common.site.lastfm.key');

        return $this;
    }

    /**
     * Load users that current user is following.
     */
    private function loadUserFollowedUsers()
    {
        $this->data['user'] = $this->data['user']->load(['followedUsers' => function(BelongsToMany $q) {
            return $q->select('users.id', 'users.avatar');
        }]);
    }

    /**
     * Get ids of all tracks in current user's library.
     */
    private function getUserLikes()
    {
        $this->data['likes'] = DB::table('likes')
            ->where('user_id', $this->data['user']['id'])
            ->get(['likeable_id', 'likeable_type'])
            ->groupBy(function($likeable) {
                return $likeable->likeable_type::MODEL_TYPE;
            })
            ->map(function(Collection $likeableGroup) {
                return $likeableGroup->mapWithKeys(function($likeable) {
                    return [$likeable->likeable_id => true];
                });
            });
    }

    /**
     * Get ids of all tracks in current user's library.
     */
    private function getUserPlaylists()
    {
        $this->data['playlists'] = $this->data['user']
            ->playlists()
            ->select('playlists.id', 'playlists.name', 'playlists.collaborative', 'playlists.owner_id')
            ->get()
            ->toArray();
    }

    private function loadManagedArtists()
    {
        $this->data['user']['artists'] = $this->data['user']
            ->artists()
            ->get(['artists.id', 'name', 'image_small'])
            ->map(function(Artist $artist) {
                return [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'image_small' => $artist->image_small,
                    'role' => $artist->pivot->role,
                ];
            });
    }
}
