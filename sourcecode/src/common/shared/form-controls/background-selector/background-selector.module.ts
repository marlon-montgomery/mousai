import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {BackgroundSelectorComponent} from '@common/shared/form-controls/background-selector/background-selector.component';
import {MatRippleModule} from '@angular/material/core';
import {FormatPipesModule} from '@common/core/ui/format-pipes/format-pipes.module';
import {LabelFromFilenamePipe} from '@common/shared/form-controls/background-selector/label-from-filename.pipe';
import {BackgroundSelectorImgComponent} from '@common/shared/form-controls/background-selector/background-selector-img/background-selector-img.component';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {BackgroundSelectorGradientComponent} from '@common/shared/form-controls/background-selector/background-selector-gradient/background-selector-gradient.component';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {BackgroundOverlayComponent} from '@common/shared/form-controls/background-selector/background-overlay/background-overlay.component';
import {ReactiveFormsModule} from '@angular/forms';
import {MatRadioModule} from '@angular/material/radio';
import {MatButtonToggleModule} from '@angular/material/button-toggle';

@NgModule({
    declarations: [
        BackgroundSelectorComponent,
        LabelFromFilenamePipe,
        BackgroundSelectorImgComponent,
        BackgroundSelectorGradientComponent,
        BackgroundOverlayComponent,
    ],
    imports: [
        CommonModule,
        TranslationsModule,
        MatRippleModule,
        FormatPipesModule,
        MatButtonModule,
        MatIconModule,
        ReactiveFormsModule,
        MatRadioModule,
        MatButtonToggleModule,
    ],
    exports: [BackgroundSelectorComponent, BackgroundOverlayComponent],
})
export class BackgroundSelectorModule {}
