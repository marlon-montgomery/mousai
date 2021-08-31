import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {SelectArtistControlComponent} from './select-artist-control.component';
import {MatMenuModule} from '@angular/material/menu';
import {MediaImageModule} from '../../web-player/shared/media-image/media-image.module';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {MatIconModule} from '@angular/material/icon';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {MatButtonModule} from '@angular/material/button';


@NgModule({
    declarations: [
        SelectArtistControlComponent,
    ],
    exports: [
        SelectArtistControlComponent,
    ],
    imports: [
        CommonModule,

        MatMenuModule,
        MediaImageModule,
        TranslationsModule,
        FormsModule,
        ReactiveFormsModule,
        MatIconModule,
        LoadingIndicatorModule,
        MatButtonModule,
    ]
})
export class SelectArtistControlModule {
}
