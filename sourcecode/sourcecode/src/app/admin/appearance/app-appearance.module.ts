import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {BaseAppearanceModule} from '@common/admin/appearance/base-appearance.module';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {ReactiveFormsModule} from '@angular/forms';
import {HomepageAppearancePanelComponent} from './homepage-appearance-panel/homepage-appearance-panel.component';
import {MatSliderModule} from '@angular/material/slider';
import {ColorPickerInputModule} from '@common/core/ui/color-picker/color-picker-input/color-picker-input.module';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {RouterModule} from '@angular/router';
import {DragDropModule} from '@angular/cdk/drag-drop';
import {APPEARANCE_EDITOR_CONFIG} from '../../../common/admin/appearance/appearance-editor-config.token';
import {APP_APPEARANCE_CONFIG} from './app-appearance-config';


@NgModule({
    declarations: [
        HomepageAppearancePanelComponent,
    ],
    imports: [
        CommonModule,
        BaseAppearanceModule,
        ReactiveFormsModule,
        ColorPickerInputModule,
        LoadingIndicatorModule,
        RouterModule,

        // material
        MatIconModule,
        MatButtonModule,
        TranslationsModule,
        MatSliderModule,
        MatAutocompleteModule,
        DragDropModule,
    ],
    providers: [
        {
            provide: APPEARANCE_EDITOR_CONFIG,
            useValue: APP_APPEARANCE_CONFIG,
            multi: true,
        }
    ]
})
export class AppAppearanceModule {
}
