import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ImageGalleryOverlayComponent} from '@common/shared/image-gallery-overlay/image-gallery-overlay.component';
import {MatIconModule} from '@angular/material/icon';
import {MediaImageModule} from '../../../app/web-player/shared/media-image/media-image.module';
import {MatButtonModule} from '@angular/material/button';


@NgModule({
    declarations: [
        ImageGalleryOverlayComponent,
    ],
    exports: [
        ImageGalleryOverlayComponent,
    ],
    imports: [
        CommonModule,
        MatIconModule,
        MediaImageModule,
        MatButtonModule,
    ]
})
export class ImageGalleryOverlayModule {
}
