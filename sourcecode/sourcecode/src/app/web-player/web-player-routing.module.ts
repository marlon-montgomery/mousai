import {NgModule} from '@angular/core';
import {RouterModule, Routes, UrlMatchResult, UrlSegment} from '@angular/router';
import {AlbumComponent} from './albums/album/album.component';
import {AlbumResolver} from './albums/album/album-resolver.service';
import {LibraryTracksComponent} from './users/user-library/library-tracks/library-tracks.component';
import {LibraryAlbumsComponent} from './users/user-library/library-albums/library-albums.component';
import {LibraryArtistsComponent} from './users/user-library/library-artists/library-artists.component';
import {ArtistPageComponent} from './artists/artist-page/artist-page.component';
import {ArtistResolver} from './artists/artist-page/artist-resolver.service';
import {PlaylistComponent} from './playlists/playlist/playlist.component';
import {PlaylistResolver} from './playlists/playlist/playlist-resolver.service';
import {SearchComponent} from './search/search/search.component';
import {SearchResolver} from './search/search/search-resolver.service';
import {SearchTabValidGuard} from './search/search/search-tab-valid.guard';
import {TrackPageComponent} from './tracks/track-page/track-page.component';
import {TrackPageResolver} from './tracks/track-page/track-page-resolver.service';
import {RadioPageComponent} from './radio-page/radio-page.component';
import {RadioPageResolver} from './radio-page/radio-page-resolver.service';
import {UserLibraryComponent} from './users/user-library/user-library.component';
import {LibraryPlaylistsComponent} from './users/user-library/library-playlists/library-playlists.component';
import {CheckPermissionsGuard} from '@common/guards/check-permissions-guard.service';
import {AuthGuard} from '@common/guards/auth-guard.service';
import {AccountSettingsResolve} from '@common/account-settings/account-settings-resolve.service';
import {AccountSettingsComponent} from '@common/account-settings/account-settings.component';
import {ChannelShowComponent} from './channels/channel-show/channel-show.component';
import {ChannelResolverService} from '../admin/channels/crupdate-channel-page/channel-resolver.service';
import {PlayHistoryComponent} from './users/user-library/play-history/play-history.component';
import {TrackEmbedComponent} from './tracks/track-embed/track-embed.component';
import {ChannelFallbackHostComponent} from './channels/channel-fallback-host/channel-fallback-host.component';
import {TagMediaPageComponent} from './tags/tag-media-page/tag-media-page.component';
import {HomepageHostComponent} from './homepage-host/homepage-host.component';
import {CustomPageComponent} from '@common/pages/shared/custom-page/custom-page.component';
import {NOT_FOUND_ROUTES} from '@common/pages/not-found-routes';
import {UserProfilePageComponent} from './users/user-profile-page/user-profile-page.component';
import {UserProfilePageResolver} from './users/user-profile-page/user-profile-page-resolver.service';
import {EmptyRouteComponent} from '@common/core/ui/empty-route/empty-route.component';

function ArtistPageMatcher(segments: UrlSegment[]): UrlMatchResult {
    if (segments.length && segments[0].path === 'artist') {
        if (segments.length === 2) {
            return {
                consumed: segments,
                posParams: {id: segments[1]},
            };
        } else if (segments.length === 3) {
            return {
                consumed: segments,
                posParams: {id: segments[1], name: segments[2]},
            };
        }
    }
    return null;
}

function channelPageMatcher(segments: UrlSegment[]): UrlMatchResult {
    if (segments.length && segments[0].path === 'channel') {
        if (segments.length === 2) {
            return {
                consumed: segments,
                posParams: {slug: segments[1]},
            };
        } else if (segments.length === 3) {
            return {
                consumed: segments,
                posParams: {slug: segments[1], filter: segments[2]},
            };
        }
    }
    return null;
}

const routes: Routes = [
    {
        path: 'track/:id/:name/embed',
        component: TrackEmbedComponent,
        resolve: {api: TrackPageResolver},
        data: {name: 'embed track'}
    },
    {
        path: 'album/:id/:artist/:album/embed',
        component: TrackEmbedComponent,
        resolve: {api: AlbumResolver},
        data: {name: 'embed album'}
    },
    {
        path: 'playlist/:id/:name/embed',
        component: TrackEmbedComponent,
        resolve: {api: PlaylistResolver},
        data: {name: 'embed playlist'}
    },
    {
        path: '',
        component: HomepageHostComponent,
        canActivateChild: [CheckPermissionsGuard],
        data: {willSetSeo: true, parentHomeRoute: true},
        children: [
            {
                path: '',
                component: ChannelShowComponent,
                resolve: {api: ChannelResolverService},
                data: {name: 'channel', channelName: 'discover', permissions: ['music.view']}
            },
            {
                path: 'user/:id/:name',
                component: UserProfilePageComponent,
                resolve: {api: UserProfilePageResolver},
                data: {name: 'user', noReuse: true},
                children: [
                    {path: ':tabName', component: EmptyRouteComponent},
                ]
            },


            {path: 'channels/:id', redirectTo: 'channel/:id'},
            {
                matcher: channelPageMatcher,
                component: ChannelShowComponent,
                resolve: {api: ChannelResolverService},
                data: {name: 'channel', failRedirectUri: '/', noReuse: true, permissions: ['music.view']}
            },


            {
                path: 'album/:id/:artist/:album',
                component: AlbumComponent,
                resolve: {api: AlbumResolver},
                data: {name: 'album', permissions: ['music.view']}
            },

            {
                path: 'genre/:name',
                component: ChannelShowComponent,
                data: {name: 'genre', permissions: ['music.view']}
            },

            {
                path: 'tag/:name',
                data: {name: 'tag', permissions: ['music.view']},
                children: [
                    {path: '',  component: TagMediaPageComponent},
                    {path: 'tracks',  component: TagMediaPageComponent},
                    {path: 'albums',  component: TagMediaPageComponent},
                ]
            },

            {
                path: 'playlist/:id',
                component: PlaylistComponent,
                resolve: {api: PlaylistResolver},
                data: {name: 'playlist', noReuse: true, permissions: ['playlists.view']}
            },
            {
                path: 'playlist/:id/:name',
                component: PlaylistComponent,
                resolve: {api: PlaylistResolver},
                data: {name: 'playlist', noReuse: true, permissions: ['playlists.view']}
            },

            // TRACKS
            {
                path: 'track/:id',
                component: TrackPageComponent,
                resolve: {api: TrackPageResolver},
                data: {name: 'track', permissions: ['music.view']}
            },
            {
                path: 'track/:id/:name',
                component: TrackPageComponent,
                resolve: {api: TrackPageResolver},
                data: {name: 'track', permissions: ['music.view']}
            },

            {path: 'search', component: SearchComponent, data: {name: 'search'}},
            {
                path: 'search/:query',
                component: SearchComponent,
                resolve: {results: SearchResolver},
                data: {name: 'search'}
            },
            {
                path: 'search/:query/:tab',
                component: SearchComponent,
                resolve: {results: SearchResolver},
                canActivate: [SearchTabValidGuard],
                data: {name: 'search'}
            },

            {
                matcher: ArtistPageMatcher,
                component: ArtistPageComponent,
                resolve: {api: ArtistResolver},
                data: {name: 'artist'},
            },

            {
                path: 'radio/:type/:id/:name',
                component: RadioPageComponent,
                resolve: {radio: RadioPageResolver},
                data: {name: 'radio'}
            },

            {
                path: 'library',
                component: UserLibraryComponent,
                canActivate: [AuthGuard],
                canActivateChild: [AuthGuard],
                children: [
                    {path: '', redirectTo: 'songs', pathMatch: 'full'},
                    {
                        path: 'songs',
                        component: LibraryTracksComponent,
                        data: {name: 'library.tracks', title: 'Your Tracks'}
                    },
                    {
                        path: 'albums',
                        component: LibraryAlbumsComponent,
                        data: {name: 'library.albums', title: 'Your Albums'}
                    },
                    {
                        path: 'artists',
                        component: LibraryArtistsComponent,
                        data: {name: 'library.artists', title: 'Your Artists'}
                    },
                    {
                        path: 'playlists',
                        component: LibraryPlaylistsComponent,
                        data: {name: 'library.playlists', title: 'Your Playlists'}
                    },
                    {
                        path: 'history',
                        component: PlayHistoryComponent,
                        data: {name: 'library.history', title: 'Listening History'}
                    },
                ]
            },

            {path: 'account-settings', redirectTo: 'account/settings'},
            {
                path: 'account/settings',
                component: AccountSettingsComponent,
                resolve: {api: AccountSettingsResolve},
                canActivate: [AuthGuard],
                data: {name: 'account-settings'}
            },

            {
                path: 'pages/:id/:slug',
                component: CustomPageComponent,
                data: {permissions: ['custom_pages.view'], willSetSeo: true}
            },

            {
                path: ':slug',
                resolve: {api: ChannelResolverService},
                component: ChannelFallbackHostComponent,
                data: {noReuse: true},
            },

            ...NOT_FOUND_ROUTES,
        ]
    },
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class WebPlayerRoutingModule {
}


