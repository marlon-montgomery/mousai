<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\AppHomeController;
use App\Http\Controllers\Artist\ArtistFollowersController;
use App\Http\Controllers\Artist\ArtistTracksController;
use App\Http\Controllers\ArtistAlbumsController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\BackstageRequestController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ImportMediaController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PlaylistTracksController;
use App\Http\Controllers\PlaylistTracksOrderController;
use App\Http\Controllers\RadioController;
use App\Http\Controllers\Search\AlbumSearchSuggestionsController;
use App\Http\Controllers\Search\ArtistSearchSuggestionsController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\TagMediaController;
use App\Http\Controllers\TrackCommentsController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UserLibrary\UserLibraryAlbumsController;
use App\Http\Controllers\UserLibrary\UserLibraryArtistsController;
use App\Http\Controllers\UserLibrary\UserLibraryTracksController;
use App\Http\Controllers\UserProfile\UserFollowedUsersController;
use App\Http\Controllers\UserProfile\UserFollowersController;
use App\Http\Controllers\UserProfile\UserPlaylistsController;
use App\Http\Controllers\UserProfileController;

Route::group(['prefix' => 'secure'], function () {
    // SEARCH
    Route::get('search/audio/{trackId}/{artistName}/{trackName}', [SearchController::class, 'searchAudio']);
    Route::get('search', [SearchController::class, 'index']);
    Route::get('search/suggestions/artists', [ArtistSearchSuggestionsController::class, 'index']);
    Route::get('search/suggestions/albums', [AlbumSearchSuggestionsController::class, 'index']);

    // PLAYLISTS
    Route::get('playlists/{id}', 'PlaylistController@show');
    Route::get('playlists', 'PlaylistController@index');
    Route::put('playlists/{playlist}', [PlaylistController::class, 'update']);
    Route::post('playlists', [PlaylistController::class, 'store']);
    Route::delete('playlists', [PlaylistController::class, 'destroy']);
    Route::post('playlists/{id}/follow', [UserPlaylistsController::class, 'follow']);
    Route::post('playlists/{id}/unfollow', [UserPlaylistsController::class, 'unfollow']);
    Route::get('playlists/{id}/tracks', [PlaylistTracksController::class, 'index']);
    Route::post('playlists/{id}/tracks/add', [PlaylistTracksController::class, 'add']);
    Route::post('playlists/{id}/tracks/remove', [PlaylistTracksController::class, 'remove']);
    Route::post('playlists/{playlist}/tracks/order', [PlaylistTracksOrderController::class, 'change']);

    // ARTISTS
    Route::get('artists', 'ArtistController@index');
    Route::post('artists', 'ArtistController@store');
    Route::put('artists/{artist}', 'ArtistController@update');
    Route::get('artists/{artist}', [ArtistController::class, 'show']);
    Route::delete('artists', 'ArtistController@destroy');
    Route::get('artists/{artist}/tracks', [ArtistTracksController::class, 'index']);
    Route::get('artists/{artist}/albums', [ArtistAlbumsController::class, 'index']);
    Route::get('artists/{artist}/followers', [ArtistFollowersController::class, 'index']);

    // ALBUMS
    Route::get('albums', 'AlbumController@index');
    Route::get('albums/{album}', 'AlbumController@show');
    Route::post('albums', 'AlbumController@store');
    Route::put('albums/{album}', 'AlbumController@update');
    Route::delete('albums', 'AlbumController@destroy');

    // TRACKS
    Route::get('tracks/{id}/wave', 'WaveController@show');
    Route::get('tracks', 'TrackController@index');
    Route::get('tracks/{id}/download', 'DownloadLocalTrackController@download');
    Route::post('tracks', 'TrackController@store');
    Route::put('tracks/{id}', 'TrackController@update');
    Route::get('tracks/{track}', [TrackController::class, 'show']);
    Route::delete('tracks', 'TrackController@destroy');
    Route::get('tracks/{track}/comments', [TrackCommentsController::class, 'index']);

    // LYRICS
    Route::get('lyrics', 'LyricsController@index');
    Route::post('lyrics', 'LyricsController@store');
    Route::delete('lyrics', 'LyricsController@destroy');
    Route::get('tracks/{id}/lyrics', 'LyricsController@show');
    Route::put('lyrics/{id}', 'LyricsController@update');

    // TAGS
    Route::get('tags/{tagName}/{mediaType}', [TagMediaController::class, 'index']);

    // GENRES
    Route::get('genres', 'GenreController@index');
    Route::post('genres', 'GenreController@store');
    Route::put('genres/{id}', 'GenreController@update');
    Route::delete('genres', 'GenreController@destroy');
    Route::get('genres/{name}', 'GenreController@show');

    // USER PROFILE
    Route::get('users/{user}', [UserProfileController::class, 'show']);
    Route::get('users/{user}/liked-tracks', [UserLibraryTracksController::class, 'index']);
    Route::get('users/{user}/liked-albums', [UserLibraryAlbumsController::class, 'index']);
    Route::get('users/{user}/liked-artists', [UserLibraryArtistsController::class, 'index']);
    Route::get('users/{user}/playlists', [UserPlaylistsController::class, 'index']);
    Route::get('users/{user}/followers', [UserFollowersController::class, 'index']);
    Route::get('users/{user}/followed-users', [UserFollowedUsersController::class, 'index']);
    Route::put('users/profile/update', [UserProfileController::class, 'update']);
    Route::post('users/me/add-to-library', [UserLibraryTracksController::class, 'addToLibrary']);
    Route::delete('users/me/remove-from-library', [UserLibraryTracksController::class, 'removeFromLibrary']);

    // UPLOAD
    Route::post('music/upload', 'MusicUploadController@upload');

    // LANDING
    Route::get('landing/channels', 'LandingPageChannelController@index');

    // YOUTUBE
    Route::post('youtube/log-client-error', 'YoutubeLogController@store');

    // TRACK PLAYS
    Route::post('player/tracks', 'PlayerTracksController@index');
    Route::get('track/plays/{userId}', 'TrackPlaysController@index');
    Route::post('track/plays/{track}/log', 'TrackPlaysController@create');

    // RADIO
    Route::get('radio/{type}/{id}', [RadioController::class, 'getRecommendations']);

    // USER FOLLOWERS
    Route::post('users/{id}/follow', 'UserFollowersController@follow');
    Route::post('users/{id}/unfollow', 'UserFollowersController@unfollow');

    // REPOSTS
    Route::get('reposts', 'RepostController@index');
    Route::post('reposts', 'RepostController@repost');

    // CHANNELS
    Route::post('channel/{channel}/detach-item', 'ChannelController@detachItem');
    Route::post('channel/{channel}/attach-item', 'ChannelController@attachItem');
    Route::post('channel/{channel}/change-order', 'ChannelController@changeOrder');
    Route::apiResource('channel', 'ChannelController');

    // NOTIFICATIONS
    Route::get('notifications', 'NotificationController@index');
    Route::post('notifications/mark-as-read', 'NotificationController@markAsRead');

    // BACKSTAGE REQUESTS
    Route::post('backstage-request/{backstageRequest}/approve', [BackstageRequestController::class, 'approve']);
    Route::post('backstage-request/{backstageRequest}/deny', [BackstageRequestController::class, 'deny']);
    Route::apiResource('backstage-request', 'BackstageRequestController');

    // IMPORT
    Route::post('import-media/single-item', [ImportMediaController::class, 'import']);
});

//FRONT-END ROUTES THAT NEED TO BE PRE-RENDERED
Route::get('/', [AppHomeController::class, 'show'])->middleware('prerenderIfCrawler');
Route::get('artist/{artist}', 'ArtistController@show')->middleware('prerenderIfCrawler');
Route::get('artist/{artist}/{name}', 'ArtistController@show')->middleware('prerenderIfCrawler');
Route::get('album/{album}/{artistName}/{albumName}', 'AlbumController@show')->middleware('prerenderIfCrawler');
Route::get('track/{track}', 'TrackController@show')->middleware('prerenderIfCrawler');
Route::get('track/{track}/{name}', 'TrackController@show')->middleware('prerenderIfCrawler');
Route::get('playlist/{id}', 'PlaylistController@show')->middleware('prerenderIfCrawler');
Route::get('playlist/{id}/{name}', 'PlaylistController@show')->middleware('prerenderIfCrawler');
Route::get('user/{id}', '\Common\Auth\Controllers\UserController@show')->middleware('prerenderIfCrawler');
Route::get('user/{id}/{name}', '\Common\Auth\Controllers\UserController@show')->middleware('prerenderIfCrawler');
Route::get('genre/{name}', 'GenreController@show')->middleware('prerenderIfCrawler');
Route::get('channel/{channel}', [ChannelController::class, 'show'])->middleware('prerenderIfCrawler');
Route::get('search/{query}', [SearchController::class, 'index'])->middleware('prerenderIfCrawler');
Route::get('search/{query}/{tab}', [SearchController::class, 'index'])->middleware('prerenderIfCrawler');

// REDIRECT LEGACY ROUTES
Route::get('genre/{name}', function($genre) {
    return redirect("channel/genre/$genre", 301);
});

Route::get('channels/{name}', function($name) {
    return redirect("channel/$name", 301);
});

//CATCH ALL ROUTES AND REDIRECT TO HOME
Route::get('{all}', [AppHomeController::class, 'show'])->where('all', '.*');
