<?php

namespace App\Http\Controllers;

use App\Album;
use App\Artist;
use App\Genre;
use App\Playlist;
use App\Services\Albums\ShowAlbum;
use App\Services\Artists\LoadArtist;
use App\Services\Providers\Spotify\SpotifyGenreArtists;
use App\Services\Providers\Spotify\SpotifyPlaylist;
use App\Services\Providers\Spotify\SpotifyTopTracks;
use App\Services\Providers\Spotify\SpotifyTrack;
use App\Track;
use Auth;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class ImportMediaController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->middleware('isAdmin');
        $this->request = $request;
    }

    public function import()
    {
        $modelType = $this->request->get('modelType');
        $spotifyId = $this->request->get('spotifyId');

        switch ($modelType) {
            case Artist::MODEL_TYPE:
                $artist = app(LoadArtist::class)
                    ->updateArtistFromExternal(Artist::firstOrCreate(['spotify_id' => $spotifyId]), $this->request->all());
                return $this->success(['artist' => $artist]);
            case Album::MODEL_TYPE:
                $response = app(ShowAlbum::class)
                    ->updateAlbum(Album::firstOrCreate(['spotify_id' => $spotifyId], ['owner_id' => Auth::id()]));
                return $this->success($response);
            case Track::MODEL_TYPE:
                $spotifyTrack = app(SpotifyTrack::class)
                    ->getContent(Track::firstOrCreate(['spotify_id' => $spotifyId], ['owner_id' => Auth::id()]));
                $track = app(SpotifyTopTracks::class)->saveAndLoad([$spotifyTrack])->first();
                if ($this->request->get('importLyrics')) {
                    app(LyricsController::class)->fetchLyrics($track->id);
                }
                return $this->success(['track' => $track]);
            case Playlist::MODEL_TYPE:
                $data = app(SpotifyPlaylist::class)->getContent($spotifyId);
                if ( ! $data) {
                    return $this->error('Could not import playlist');
                }
                Playlist::unguard();
                $playlist = Playlist::firstOrCreate(['spotify_id' => $spotifyId], ['owner_id' => Auth::id()]);
                $playlist->editors()->syncWithoutDetaching([Auth::id()]);
                $playlist->fill($data['playlist'])->save();
                $playlist->tracks()->sync($data['tracks']);
                return $this->success(['playlist' => $playlist]);
            case Genre::MODEL_TYPE:
                $genre = Genre::find($this->request->get('genreId'));
                $artists = app(SpotifyGenreArtists::class)->getContent($genre);
                return $this->success(['genre' => $genre, 'artists' => $artists]);
        }
    }
}
