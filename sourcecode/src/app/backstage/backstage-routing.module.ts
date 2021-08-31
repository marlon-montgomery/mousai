import {RouterModule, Routes} from '@angular/router';
import {NgModule} from '@angular/core';
import {BackstageRequestFormComponent} from './requests/backstage-request-form/backstage-request-form.component';
import {BackstageRequestSubmittedComponent} from './requests/backstage-request-submitted/backstage-request-submitted.component';
import {BackstageTypeSelectorComponent} from './requests/backstage-type-selector/backstage-type-selector.component';
import {CrupdateArtistPageComponent} from './editing/crupdate-artist-page/crupdate-artist-page.component';
import {EditArtistPageResolver} from './editing/crupdate-artist-page/crupdate-artist-page-resolver.service';
import {CrupdateAlbumPageComponent} from './editing/crupdate-album-page/crupdate-album-page.component';
import {PendingChangesGuard} from '@common/guards/pending-changes/pending-changes-guard.service';
import {CrupdateTrackPageComponent} from './editing/crupdate-track-page/crupdate-track-page.component';
import {TrackPageResolver} from '../web-player/tracks/track-page/track-page-resolver.service';
import {UploadPageComponent} from '../uploading/upload-page/upload-page.component';
import {CrupdateAlbumPageResolverService} from './editing/crupdate-album-page/crupdate-album-page-resolver.service';

const routes: Routes = [
    {
        path: '',
        redirectTo: '/',
        pathMatch: 'full',
    },
    {
        path: 'requests',
        component: BackstageTypeSelectorComponent,
    },
    {
        path: 'requests/verify-artist',
        component: BackstageRequestFormComponent,
    },
    {
        path: 'requests/become-artist',
        component: BackstageRequestFormComponent,
    },
    {
        path: 'requests/claim-artist',
        component: BackstageRequestFormComponent,
    },
    {
        path: 'requests/:requestId/request-submitted',
        component: BackstageRequestSubmittedComponent,
    },

    //
    {
        path: 'upload',
        component: UploadPageComponent,
    },
    {
        path: 'artists/new',
        component: CrupdateArtistPageComponent,
    },
    {
        path: 'artists/:id/edit',
        component: CrupdateArtistPageComponent,
        resolve: {api: EditArtistPageResolver},
        canDeactivate: [PendingChangesGuard],
    },
    {
        path: 'albums/:id/edit',
        component: CrupdateAlbumPageComponent,
        resolve: {api: CrupdateAlbumPageResolverService},
        canDeactivate: [PendingChangesGuard],
    },
    {
        path: 'albums/new',
        component: CrupdateAlbumPageComponent,
        canDeactivate: [PendingChangesGuard],
    },
    {
        path: 'tracks/:id/edit',
        component: CrupdateTrackPageComponent,
        resolve: {api: TrackPageResolver},
        canDeactivate: [PendingChangesGuard],
    },
    {
        path: 'tracks/new',
        component: CrupdateTrackPageComponent,
        canDeactivate: [PendingChangesGuard],
    },
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class BackstageRoutingModule {
}
