import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TrackActionsBarComponent} from './track-actions-bar.component';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {MatTooltipModule} from '@angular/material/tooltip';


@NgModule({
    declarations: [
        TrackActionsBarComponent,
    ],
    imports: [
        CommonModule,
        TranslationsModule,

        // material
        MatIconModule,
        MatButtonModule,
        MatTooltipModule,
    ],
    exports: [
        TrackActionsBarComponent,
    ]
})
export class TrackActionsBarModule {
}
