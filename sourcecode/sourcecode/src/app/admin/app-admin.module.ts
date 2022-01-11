import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {ArtistIndexComponent} from './artists/artist-index.component';
import {CrupdateLyricModalComponent} from './lyrics-page/crupdate-lyric-modal/crupdate-lyric-modal.component';
import {AlbumIndexComponent} from './albums/album-index/album-index.component';
import {LyricsPageComponent} from './lyrics-page/lyrics-page.component';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {MatChipsModule} from '@angular/material/chips';
import {MatProgressBarModule} from '@angular/material/progress-bar';
import {MatTabsModule} from '@angular/material/tabs';
import {PlaylistsPageComponent} from './playlists-page/playlists-page.component';
import {ProvidersSettingsComponent} from './settings/providers/providers-settings.component';
import {PlayerSettingsComponent} from './settings/player/player-settings.component';
import {BlockedArtistsSettingsComponent} from './settings/blocked-artists/blocked-artists-settings.component';
import {GenresComponent} from './genres/genres.component';
import {CrupdateGenreModalComponent} from './genres/crupdate-genre-modal/crupdate-genre-modal.component';
import {BaseAdminModule} from '@common/admin/base-admin.module';
import {UploadsModule} from '@common/uploads/uploads.module';
import {MediaImageModule} from '../web-player/shared/media-image/media-image.module';
import {TrackIndexComponent} from './tracks/track-index/track-index.component';
import {ChannelIndexComponent} from './channels/channel-index/channel-index.component';
import {CrupdateChannelPageComponent} from './channels/crupdate-channel-page/crupdate-channel-page.component';
import {DragDropModule} from '@angular/cdk/drag-drop';
import {SlugControlModule} from '@common/shared/form-controls/slug-control/slug-control.module';
import {InterfaceComponent} from './settings/interface/interface.component';
import {UploadImageControlModule} from '@common/shared/form-controls/upload-image-control/upload-image-control.module';
import {NoResultsMessageModule} from '@common/core/ui/no-results-message/no-results-message.module';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {InfoPopoverModule} from '@common/core/ui/info-popover/info-popover.module';
import {MatPseudoCheckboxModule, MatRippleModule} from '@angular/material/core';
import {BackstageRequestsIndexComponent} from './backstage-requests-index/backstage-requests-index.component';
import {BackstageRequestViewerComponent} from './backstage-requests-index/backstage-request-viewer/backstage-request-viewer.component';
import {SkeletonModule} from '@common/core/ui/skeleton/skeleton.module';
import {ConfirmRequestHandledModalComponent} from './backstage-requests-index/backstage-request-viewer/confirm-request-handled-modal/confirm-request-handled-modal.component';
import {BetweenDateInputModule} from '@common/core/ui/between-date-input/between-date-input.module';
import {SelectUserInputModule} from '@common/core/ui/select-user-input/select-user-input.module';

// tslint:disable-next-line:max-line-length
import {BackstageRequestsFiltersComponent} from './backstage-requests-index/backstage-requests-filters/backstage-requests-filters.component';

import {CommentIndexComponent} from './comments/comment-index.component';
import {ImportMediaModalComponent} from './import-media-modal/import-media-modal.component';
import {FormattedChannelConfigPipe} from './channels/channel-index/formatted-channel-config.pipe';
import {EnterKeybindDirective} from '@common/core/ui/enter-keybind.directive';

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        BaseAdminModule,
        UploadsModule,
        NoResultsMessageModule,
        LoadingIndicatorModule,
        InfoPopoverModule,
        SkeletonModule,
        SlugControlModule,
        UploadImageControlModule,
        BetweenDateInputModule,
        SelectUserInputModule,
        MediaImageModule,

        // material
        MatChipsModule,
        MatAutocompleteModule,
        MatProgressBarModule,
        DragDropModule,
        MatTabsModule,
        MatPseudoCheckboxModule,
        MatRippleModule,
    ],
    declarations: [
        EnterKeybindDirective,
        ArtistIndexComponent,
        CrupdateLyricModalComponent,
        TrackIndexComponent,
        AlbumIndexComponent,
        LyricsPageComponent,
        PlaylistsPageComponent,
        ChannelIndexComponent,
        CommentIndexComponent,
        BackstageRequestsIndexComponent,
        BackstageRequestsFiltersComponent,
        BackstageRequestViewerComponent,
        ConfirmRequestHandledModalComponent,
        ImportMediaModalComponent,
        GenresComponent,
        CrupdateGenreModalComponent,
        CrupdateChannelPageComponent,

        // settings
        ProvidersSettingsComponent,
        PlayerSettingsComponent,
        BlockedArtistsSettingsComponent,
        InterfaceComponent,
        FormattedChannelConfigPipe,
    ]
})
export class AppAdminModule {
}
