<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Artist\ArtistFollowersController;
use App\Http\Controllers\Artist\ArtistTracksController;
use App\Http\Controllers\ArtistAlbumsController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PlaylistTracksController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\TagMediaController;
use App\Http\Controllers\TrackCommentsController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UserFollowersController;
use App\Http\Controllers\UserLibrary\UserLibraryAlbumsController;
use App\Http\Controllers\UserLibrary\UserLibraryArtistsController;
use App\Http\Controllers\UserLibrary\UserLibraryTracksController;
use App\Http\Controllers\UserProfile\UserFollowedUsersController;
use App\Http\Controllers\UserProfile\UserPlaylistsController;
use App\Http\Controllers\UserProfileController;
use Common\Auth\Controllers\GetAccessTokenController;
use Common\Auth\Controllers\RegisterController;

Route::group(['prefix' => 'v1'], function() {
    Route::group(['middleware' => 'auth:sanctum'], function() {

        // SEARCH
        Route::get('search', [SearchController::class, 'index']);

        // PLAYLISTS
        Route::get('playlists/{id}', 'PlaylistController@show');
        Route::put('playlists/{playlist}', [PlaylistController::class, 'update']);
        Route::post('playlists', [PlaylistController::class, 'store']);
        Route::delete('playlists', [PlaylistController::class, 'destroy']);
        Route::post('playlists/{id}/follow', [UserPlaylistsController::class, 'follow']);
        Route::post('playlists/{id}/unfollow', [UserPlaylistsController::class, 'unfollow']);
        Route::get('playlists/{id}/tracks', [PlaylistTracksController::class, 'index']);
        Route::post('playlists/{id}/tracks/add', [PlaylistTracksController::class, 'add']);
        Route::post('playlists/{id}/tracks/remove', [PlaylistTracksController::class, 'remove']);

        // ARTISTS
        Route::get('artists/{artist}', [ArtistController::class, 'show']);
        Route::get('artists/{artist}/tracks', [ArtistTracksController::class, 'index']);
        Route::get('artists/{artist}/albums', [ArtistAlbumsController::class, 'index']);
        Route::get('artists/{artist}/followers', [ArtistFollowersController::class, 'index']);

        // ALBUMS
        Route::get('albums/{album}', 'AlbumController@show');

        // TRACKS
        Route::get('tracks/{track}', [TrackController::class, 'show']);
        Route::get('tracks/{track}/comments', [TrackCommentsController::class, 'index']);

        // LYRICS
        Route::get('tracks/{id}/lyrics', 'LyricsController@show');

        // TAGS
        Route::get('tags/{tagName}/{mediaType}', [TagMediaController::class, 'index']);

        // GENRES
        Route::get('genres', 'GenreController@index');

        // USER PROFILE
        Route::get('users/{user}', [UserProfileController::class, 'show']);
        Route::get('users/me/liked-tracks', [UserLibraryTracksController::class, 'index']);
        Route::get('users/me/liked-albums', [UserLibraryAlbumsController::class, 'index']);
        Route::get('users/me/liked-artists', [UserLibraryArtistsController::class, 'index']);
        Route::get('users/me/playlists', [UserPlaylistsController::class, 'index']);
        Route::get('users/me/followers', [UserFollowersController::class, 'index']);
        Route::get('users/me/followed-users', [UserFollowedUsersController::class, 'index']);
    });

    // AUTH
    Route::post('auth/register', [RegisterController::class, 'register']);
    Route::post('auth/login', [GetAccessTokenController::class, 'login']);
    Route::get('auth/social/{provider}/callback', '\Common\Auth\Controllers\SocialAuthController@loginCallback');
    Route::post('auth/password/email', '\Common\Auth\Controllers\SendPasswordResetEmailController@sendResetLinkEmail');
});


