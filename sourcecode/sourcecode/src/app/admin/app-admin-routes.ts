import {Routes} from '@angular/router';
import {ArtistIndexComponent} from './artists/artist-index.component';
import {AlbumIndexComponent} from './albums/album-index/album-index.component';
import {GenresComponent} from './genres/genres.component';
import {LyricsPageComponent} from './lyrics-page/lyrics-page.component';
import {PlaylistsPageComponent} from './playlists-page/playlists-page.component';
import {ProvidersSettingsComponent} from './settings/providers/providers-settings.component';
import {PlayerSettingsComponent} from './settings/player/player-settings.component';
import {TrackIndexComponent} from './tracks/track-index/track-index.component';
import {ChannelIndexComponent} from './channels/channel-index/channel-index.component';
import {CrupdateChannelPageComponent} from './channels/crupdate-channel-page/crupdate-channel-page.component';
import {ChannelResolverService} from './channels/crupdate-channel-page/channel-resolver.service';
import {InterfaceComponent} from './settings/interface/interface.component';
import {BackstageRequestsIndexComponent} from './backstage-requests-index/backstage-requests-index.component';
import {BackstageRequestViewerComponent} from './backstage-requests-index/backstage-request-viewer/backstage-request-viewer.component';
import {SearchSettingsComponent} from './settings/search/search-settings.component';
import {CommentIndexComponent} from './comments/comment-index.component';

export const APP_ADMIN_ROUTES: Routes = [
    {path: 'backstage', loadChildren: () => import('src/app/backstage/backstage.module').then(m => m.BackstageModule)},

    // CHANNELS
    {
        path: 'channels',
        component: ChannelIndexComponent,
        data: {permissions: ['channels.view']}
    },
    {
        path: 'channels/new',
        component: CrupdateChannelPageComponent,
        data: {permissions: ['channels.create']}
    },
    {
        path: 'channels/:id',
        component: CrupdateChannelPageComponent,
        resolve: {api: ChannelResolverService},
        data: {permissions: ['channels.update'], failRedirectUri: '/admin/channels', forAdmin: true},
    },

    {
        path: 'artists',
        component: ArtistIndexComponent,
    },
    {
        path: 'albums',
        component: AlbumIndexComponent,
        data: {permissions: ['albums.view']}
    },
    {
        path: 'tracks',
        component: TrackIndexComponent,
        data: {permissions: ['tracks.view']}
    },
    {
        path: 'genres',
        component: GenresComponent,
        data: {permissions: ['genres.view']}
    },
    {
        path: 'lyrics',
        component: LyricsPageComponent,
        data: {permissions: ['lyrics.view']}
    },
    {
        path: 'playlists',
        component: PlaylistsPageComponent,
        data: {permissions: ['playlists.view']}
    },

    // REQUESTS
    {
        path: 'backstage-requests',
        component: BackstageRequestsIndexComponent,
    },
    {
        path: 'backstage-requests/:requestId',
        component: BackstageRequestViewerComponent,
    },

    // COMMENTS
    {
        path: 'comments',
        component: CommentIndexComponent,
    }
];

export const APP_SETTING_ROUTES: Routes = [
    {path: 'providers', component: ProvidersSettingsComponent},
    {path: 'player', component: PlayerSettingsComponent},
    {path: 'interface', component: InterfaceComponent},
    {path: 'search', component: SearchSettingsComponent},
];

export const APP_ANALYTIC_ROUTES: Routes = [
    //
];
