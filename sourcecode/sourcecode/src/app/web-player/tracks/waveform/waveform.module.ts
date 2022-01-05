import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {WaveformComponent} from './waveform.component';
import {CommentBarComponent} from './comment-bar/comment-bar.component';
import {CommentOverlayComponent} from './comment-bar/comment-overlay/comment-overlay.component';
import {MediaImageModule} from '../../shared/media-image/media-image.module';


@NgModule({
    declarations: [
        WaveformComponent,
        CommentBarComponent,
        CommentOverlayComponent,
    ],
    imports: [
        CommonModule,
        MediaImageModule,
    ],
    entryComponents: [
        CommentOverlayComponent,
    ],
    exports: [
        WaveformComponent,
    ]
})
export class WaveformModule {
}
