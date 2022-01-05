<?php namespace App\Services\Providers\Local;

use App\Album;
use App\Artist;
use App\Channel;
use App\Genre;
use App\Playlist;
use App\Services\Search\SearchInterface;
use App\Tag;
use App\Track;
use App\User;
use Illuminate\Support\Collection;

class LocalSearch implements SearchInterface {

    /**
     * @var int
     */
    protected $query;

    /**
     * @var int
     */
    protected $limit;

    public function search(string $q, int $limit, array $modelTypes): array {
        $this->query = urldecode($q);
        $this->limit = $limit ?: 10;

        $results = [];

        foreach ($modelTypes as $modelType) {
            if ($modelType === Artist::MODEL_TYPE) {
                $results['artists'] = $this->artists();
            } else if ($modelType === Album::MODEL_TYPE) {
                $results['albums'] = $this->albums();
            } else if ($modelType === Track::MODEL_TYPE) {
                $results['tracks'] = $this->tracks();
            } else if ($modelType === Playlist::MODEL_TYPE) {
                $results['playlists'] = $this->playlists();
            } else if ($modelType === Channel::MODEL_TYPE) {
                $results['channels'] = $this->channels();
            } else if ($modelType === Genre::MODEL_TYPE) {
                $results['genres'] = $this->genres();
            } else if ($modelType === Tag::MODEL_TYPE) {
                $results['tags'] = $this->tags();
            } else if ($modelType === User::MODEL_TYPE) {
                $results['users'] = $this->users();
            }
        }

        return $results;
    }

    public function artists(): Collection
    {
        return Artist::search($this->query)->take($this->limit)->get();
    }

    public function albums(): Collection
    {
        return Album::search($this->query)->take($this->limit)->get()->load('artists');
    }

    public function tracks(): Collection
    {
        return Track::search($this->query)->take($this->limit)->get()->load(['album', 'artists']);
    }

    public function playlists(): Collection
    {
        return Playlist::search($this->query)->take($this->limit)->get()->load(['editors']);
    }

    public function channels(): Collection
    {
        return app(Channel::class)->search($this->query)->take($this->limit)->get();
    }

    public function genres(): Collection
    {
        return app(Genre::class)->search($this->query)->take($this->limit)->get();
    }

    public function tags(): Collection
    {
        return app(Tag::class)->search($this->query)->take($this->limit)->get();
    }

    public function users(): Collection
    {
        return app(User::class)
            ->search($this->query)
            ->take($this->limit)
            ->get()
            ->loadCount('followers')
            ->map
            ->only(['email', 'id', 'avatar', 'model_type', 'display_name']);
    }
}
