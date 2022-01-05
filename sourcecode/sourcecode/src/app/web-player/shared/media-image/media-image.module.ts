import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MediaImageComponent} from './media-image.component';
import {LazyLoadImageModule} from 'ng-lazyload-image';

@NgModule({
    declarations: [
        MediaImageComponent,
    ],
    imports: [
        CommonModule,
        LazyLoadImageModule,
    ],
    exports: [
        MediaImageComponent,
    ]
})
export class MediaImageModule {
}
