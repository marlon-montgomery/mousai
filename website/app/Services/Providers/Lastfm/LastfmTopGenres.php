<?php namespace App\Services\Providers\Lastfm;

use App;
use App\Genre;
use App\Services\HttpClient;
use App\Services\Providers\ContentProvider;
use App\Services\Providers\SaveOrUpdate;
use Common\Settings\Settings;
use File;
use Illuminate\Filesystem\Filesystem;

class LastfmTopGenres implements ContentProvider {

    use SaveOrUpdate;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var Genre
     */
    private $genre;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @param Settings $settings
     * @param Genre $genre
     * @param Filesystem $fs
     */
    public function __construct(
        Settings $settings,
        Genre $genre,
        Filesystem $fs
    ) {
        $this->httpClient = new HttpClient(['base_uri' => 'http://ws.audioscrobbler.com/2.0/']);
        $this->settings = $settings;
        $this->genre = $genre;
        $this->fs = $fs;
        $this->apiKey = config('common.site.lastfm.key');

        @ini_set('max_execution_time', 0);
    }

    public function getContent()
    {
        $response = $this->httpClient->get("?method=tag.getTopTags&api_key=$this->apiKey&format=json&num_res=100");

        // fall back to local genres, if there's an issue with last.fm
        if ( ! isset($response['toptags']['tag'])) {
            return $this->genre
                ->orderBy('popularity', 'desc')
                ->limit(50)
                ->get();
        }

        $lastfmGenres = collect($response['toptags']['tag']);

        // genres that exist on spotify
        $spotifyGenres = File::getRequire(app_path('Services/Providers/Spotify/spotify-genres.php'));
        $lastfmGenres = $lastfmGenres->filter(function($genre) use($spotifyGenres) {
            $name = slugify($genre['name']);
            return in_array($name, $spotifyGenres) && $name !== 'swedish';
        });

        // save genres
        return $lastfmGenres->map(function($genreData) {
            $name = $genreData['name'];
            if (strtolower($name) === 'hip-hop' || strtolower($name) === 'trip-hop') {
                $name = str_replace('-', ' ', $name);
            }
            $data = [
                'name' => $name,
                'image' => $this->getImage($name),
            ];
            return $this->genre->updateOrCreate(['name' => $name], $data);
        });
    }

    /**
     * Get default genre image path, if it exists.
     *
     * @param string $name
     * @return null|string
     */
    private function getImage($name)
    {
        $filename = slugify($name) . '.jpg';
        $path = "client/assets/images/genres/$filename";

        return $this->fs->exists(public_path($path)) ? $path : null;
    }
}
