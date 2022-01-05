import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MediaListItemComponent} from './media-list-item.component';
import {WaveformModule} from '../waveform/waveform.module';
import {MediaImageModule} from '../../shared/media-image/media-image.module';
import {RouterModule} from '@angular/router';
import {MatChipsModule} from '@angular/material/chips';
import {NewCommentFormModule} from '../new-comment-form/new-comment-form.module';
import {TrackActionsBarModule} from '../track-actions-bar/track-actions-bar.module';
import {PlayerUiModule} from '../../player-ui.module';
import {ArtistsLinksListModule} from '../../artists/artists-links-list/artists-links-list.module';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';


@NgModule({
    declarations: [
        MediaListItemComponent,
    ],
    imports: [
        CommonModule,
        WaveformModule,
        MediaImageModule,
        NewCommentFormModule,
        TrackActionsBarModule,
        PlayerUiModule,
        RouterModule,
        MatChipsModule,
        ArtistsLinksListModule,

        // material
        MatButtonModule,
        MatIconModule,
    ],
    exports: [
        MediaListItemComponent,
    ]
})
export class MediaListItemModule {
}
