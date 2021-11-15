import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {BackstageRoutingModule} from './backstage-routing.module';
import {MaterialNavbarModule} from '@common/core/ui/material-navbar/material-navbar.module';
import {MatButtonModule} from '@angular/material/button';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {UploadImageControlModule} from '@common/shared/form-controls/upload-image-control/upload-image-control.module';
import {BackstageRequestFormComponent} from './requests/backstage-request-form/backstage-request-form.component';
import {BackstageRequestSubmittedComponent} from './requests/backstage-request-submitted/backstage-request-submitted.component';
import {BackstageTypeSelectorComponent} from './requests/backstage-type-selector/backstage-type-selector.component';
import {MatIconModule} from '@angular/material/icon';
import {UploadsModule} from '@common/uploads/uploads.module';
import {FormatPipesModule} from '@common/core/ui/format-pipes/format-pipes.module';
import {SelectArtistControlModule} from '../shared/select-artist-control/select-artist-control.module';
import {CrupdateArtistPageComponent} from './editing/crupdate-artist-page/crupdate-artist-page.component';
import {MatTabsModule} from '@angular/material/tabs';
import {ChipsModule} from '@common/core/ui/chips/chips.module';
import {ArtistAlbumsTableComponent} from './editing/crupdate-artist-page/artist-albums-table/artist-albums-table.component';
import {DatatableModule} from '@common/datatable/datatable.module';
import {MediaImageModule} from '../web-player/shared/media-image/media-image.module';
import {MatTooltipModule} from '@angular/material/tooltip';
import {ProfileLinksFormControlModule} from '../web-player/shared/profile-links-form-control/profile-links-form-control.module';
import {CrupdateTrackPageComponent} from './editing/crupdate-track-page/crupdate-track-page.component';
import {CrupdateAlbumPageComponent} from './editing/crupdate-album-page/crupdate-album-page.component';
import {UploadingModule} from '../uploading/uploading.module';
import {MatDialogModule} from '@angular/material/dialog';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {MatSlideToggleModule} from '@angular/material/slide-toggle';

@NgModule({
    declarations: [
        BackstageRequestFormComponent,
        BackstageRequestSubmittedComponent,
        BackstageTypeSelectorComponent,

        //
        CrupdateArtistPageComponent,
        ArtistAlbumsTableComponent,
        CrupdateAlbumPageComponent,
        CrupdateTrackPageComponent,
    ],
    imports: [
        CommonModule,
        BackstageRoutingModule,
        UploadingModule,
        ReactiveFormsModule,
        FormsModule,
        SelectArtistControlModule,
        TranslationsModule,
        MaterialNavbarModule,
        MatButtonModule,
        MatIconModule,
        MatSlideToggleModule,
        UploadsModule,
        UploadImageControlModule,
        FormatPipesModule,
        ProfileLinksFormControlModule,
        LoadingIndicatorModule,

        //
        MatDialogModule,
        MatTabsModule,
        ChipsModule,
        DatatableModule,
        MediaImageModule,
        MatTooltipModule,
    ]
})
export class BackstageModule {
}
